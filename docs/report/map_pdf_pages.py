from __future__ import annotations

import argparse
import json
from pathlib import Path

from pypdf import PdfReader


def normalize(text):
    return " ".join((text or "").replace("\t", " ").split())


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("pdf")
    parser.add_argument("manifest")
    parser.add_argument("--out", required=True)
    args = parser.parse_args()

    manifest = json.loads(Path(args.manifest).read_text("utf-8"))
    reader = PdfReader(args.pdf)
    page_text = [normalize(page.extract_text()) for page in reader.pages]
    page_map = {}
    missing = []
    for group in ("headings", "figures", "tables"):
        for entry in manifest[group]:
            needle = normalize(entry["text"])
            found = None
            # Most labels appear first in a front-matter list and later at the
            # actual heading/caption, so the last occurrence is authoritative.
            # The two headings that precede the TOC are the intentional
            # exception and use their first occurrence.
            if group == "headings" and needle in ("LEMBAR IDENTITAS", "ABSTRAK"):
                candidates = range(1, len(page_text) + 1)
            else:
                candidates = range(len(page_text), 0, -1)
            for idx in candidates:
                text = page_text[idx - 1]
                if needle and needle in text:
                    found = idx
                    break
            if found:
                page_map[entry["key"]] = found
            else:
                missing.append(entry["key"])
    Path(args.out).write_text(json.dumps(page_map, indent=2), "utf-8")
    print(f"PAGES={len(reader.pages)} MAPPED={len(page_map)} MISSING={len(missing)}")
    if missing:
        print("MISSING_KEYS=" + ",".join(missing))


if __name__ == "__main__":
    main()
