#!/usr/bin/env python3
from __future__ import annotations

import argparse
import hashlib
import json
import re
import sys
import zipfile
from pathlib import Path
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parents[2]
PACKAGE_FILES = [
    "CHANGELOG.md",
    "LICENSE.txt",
    "README.md",
    "changelog.xml",
    "cookienotice.xml",
]
PACKAGE_DIRS = ["language", "layouts", "media", "services", "src"]


def fail(message: str) -> "NoReturn":
    print(f"ERROR: {message}", file=sys.stderr)
    raise SystemExit(1)


def manifest_version() -> str:
    root = ET.parse(ROOT / "cookienotice.xml").getroot()
    version = (root.findtext("version") or "").strip()
    if not re.fullmatch(r"\d+\.\d+\.\d+", version):
        fail(f"Invalid manifest version: {version!r}")
    return version


def validate() -> str:
    version = manifest_version()

    asset_path = ROOT / "media" / "joomla.asset.json"
    with asset_path.open("r", encoding="utf-8") as handle:
        asset = json.load(handle)

    if str(asset.get("version", "")).strip() != version:
        fail("media/joomla.asset.json top-level version does not match cookienotice.xml")

    for item in asset.get("assets", []):
        if str(item.get("version", "")).strip() != version:
            fail(f"Web asset {item.get('name', '<unnamed>')} version does not match cookienotice.xml")

    changelog_root = ET.parse(ROOT / "changelog.xml").getroot()
    first_changelog = changelog_root.find("changelog")
    if first_changelog is None:
        fail("changelog.xml has no <changelog> entry")
    if (first_changelog.findtext("version") or "").strip() != version:
        fail("Latest changelog.xml version does not match cookienotice.xml")

    markdown = (ROOT / "CHANGELOG.md").read_text(encoding="utf-8")
    if not re.search(rf"^##\s+{re.escape(version)}(?:\s|$)", markdown, flags=re.MULTILINE):
        fail(f"CHANGELOG.md has no section for {version}")

    for path in PACKAGE_FILES:
        if not (ROOT / path).is_file():
            fail(f"Required package file is missing: {path}")
    for path in PACKAGE_DIRS:
        if not (ROOT / path).is_dir():
            fail(f"Required package directory is missing: {path}")

    print(version)
    return version


def package_paths() -> list[Path]:
    paths = [ROOT / name for name in PACKAGE_FILES]
    for dirname in PACKAGE_DIRS:
        paths.extend(path for path in (ROOT / dirname).rglob("*") if path.is_file())
    return sorted(paths, key=lambda p: p.relative_to(ROOT).as_posix())


def build(output_dir: Path) -> tuple[str, Path, str]:
    version = validate()
    output_dir.mkdir(parents=True, exist_ok=True)
    package = output_dir / f"plg_system_cookienotice_{version}.zip"

    with zipfile.ZipFile(package, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for source in package_paths():
            relative = source.relative_to(ROOT).as_posix()
            info = zipfile.ZipInfo(relative, date_time=(1980, 1, 1, 0, 0, 0))
            info.compress_type = zipfile.ZIP_DEFLATED
            info.create_system = 3
            info.external_attr = (0o100644 & 0xFFFF) << 16
            archive.writestr(info, source.read_bytes(), compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)

    digest = hashlib.sha256(package.read_bytes()).hexdigest()
    checksum = package.with_suffix(package.suffix + ".sha256")
    checksum.write_text(f"{digest}  {package.name}\n", encoding="utf-8")

    print(f"version={version}")
    print(f"package={package}")
    print(f"sha256={digest}")
    return version, package, digest


def update_metadata(version: str, digest: str, repository: str) -> None:
    if not re.fullmatch(r"[0-9a-fA-F]{64}", digest):
        fail("Expected SHA-256 must be exactly 64 hexadecimal characters")

    path = ROOT / "updates.xml"
    text = path.read_text(encoding="utf-8")
    download_url = (
        f"https://github.com/{repository}/releases/download/v{version}/"
        f"plg_system_cookienotice_{version}.zip"
    )

    replacements = [
        (r"(<version>)[^<]+(</version>)", rf"\g<1>{version}\g<2>"),
        (r"(<downloadurl\b[^>]*>)[^<]+(</downloadurl>)", rf"\g<1>{download_url}\g<2>"),
        (r"(<sha256>)[0-9a-fA-F]+(</sha256>)", rf"\g<1>{digest.lower()}\g<2>"),
    ]

    for pattern, replacement in replacements:
        text, count = re.subn(pattern, replacement, text, count=1)
        if count != 1:
            fail(f"Could not update updates.xml using pattern: {pattern}")

    path.write_text(text, encoding="utf-8")
    ET.parse(path)


def release_notes(version: str, output: Path) -> None:
    text = (ROOT / "CHANGELOG.md").read_text(encoding="utf-8")
    match = re.search(
        rf"^##\s+{re.escape(version)}[^\n]*\n(?P<body>.*?)(?=^##\s+|\Z)",
        text,
        flags=re.MULTILINE | re.DOTALL,
    )
    if not match:
        fail(f"Could not extract release notes for {version} from CHANGELOG.md")
    body = match.group("body").strip()
    if not body:
        fail(f"CHANGELOG.md section for {version} is empty")
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(body + "\n", encoding="utf-8")


def main() -> None:
    parser = argparse.ArgumentParser(description="Smart Cookie Consent release helper")
    sub = parser.add_subparsers(dest="command", required=True)

    sub.add_parser("version")
    sub.add_parser("validate")

    build_parser = sub.add_parser("build")
    build_parser.add_argument("--output-dir", default="dist")

    update_parser = sub.add_parser("update-metadata")
    update_parser.add_argument("--version", required=True)
    update_parser.add_argument("--sha256", required=True)
    update_parser.add_argument("--repository", required=True)

    notes_parser = sub.add_parser("release-notes")
    notes_parser.add_argument("--version", required=True)
    notes_parser.add_argument("--output", required=True)

    args = parser.parse_args()

    if args.command == "version":
        print(manifest_version())
    elif args.command == "validate":
        validate()
    elif args.command == "build":
        build((ROOT / args.output_dir).resolve())
    elif args.command == "update-metadata":
        update_metadata(args.version, args.sha256, args.repository)
    elif args.command == "release-notes":
        release_notes(args.version, (ROOT / args.output).resolve())


if __name__ == "__main__":
    main()
