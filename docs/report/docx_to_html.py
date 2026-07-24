from __future__ import annotations

import argparse
import html
import json
from pathlib import Path

from docx import Document
from docx.document import Document as DocumentType
from docx.oxml.ns import qn
from docx.table import Table
from docx.text.paragraph import Paragraph


def blocks(parent):
    parent_elm = parent.element.body if isinstance(parent, DocumentType) else parent._tc
    for child in parent_elm.iterchildren():
        if child.tag == qn("w:p"):
            yield Paragraph(child, parent)
        elif child.tag == qn("w:tbl"):
            yield Table(child, parent)


def esc(text):
    return html.escape(text or "", quote=True)


def page_break(paragraph):
    return bool(paragraph._p.xpath('.//w:br[@w:type="page"]'))


def image_rel(paragraph):
    blips = paragraph._p.xpath(".//a:blip")
    if not blips:
        return None
    return blips[0].get(qn("r:embed"))


def table_html(table):
    header = ""
    rows = []
    for r_idx, row in enumerate(table.rows):
        cells = []
        tag = "th" if r_idx == 0 else "td"
        for cell in row.cells:
            text = "<br>".join(esc(p.text) for p in cell.paragraphs)
            cells.append(f"<{tag}>{text}</{tag}>")
        row_html = "<tr>" + "".join(cells) + "</tr>"
        if r_idx == 0:
            header = row_html
        else:
            rows.append(row_html)
    return (
        '<table class="report-table"><thead>'
        + header
        + "</thead><tbody>"
        + "".join(rows)
        + "</tbody></table>"
    )


def list_html(items, page_map):
    rows = []
    for key, label in items:
        page = page_map.get(key, "")
        rows.append(
            f'<div class="toc-row"><span>{esc(label)}</span>'
            f'<span class="dots"></span><span class="toc-page">{esc(str(page))}</span></div>'
        )
    return "".join(rows)


CSS = r"""
@page {
  size: A4;
  margin: 3cm 3cm 3cm 4cm;
}
* { box-sizing: border-box; }
html, body { font-family: "Times New Roman", serif; font-size: 12pt; line-height: 1.5; color: #000; }
body { margin: 0; }
p { margin: 0 0 6pt; text-align: justify; text-indent: 1.25cm; orphans: 3; widows: 3; }
p.center { text-align: center; text-indent: 0; }
p.no-indent { text-indent: 0; }
p.bullet { margin-left: 1.25cm; text-indent: -0.5cm; }
p.bullet::before { content: "•"; display: inline-block; width: 0.5cm; }
h1, h2, h3 { font-family: "Times New Roman", serif; color: #000; font-weight: bold; break-after: avoid; page-break-after: avoid; }
h1 { font-size: 14pt; margin: 18pt 0 10pt; }
h2 { font-size: 12pt; margin: 12pt 0 6pt; }
h3 { font-size: 12pt; margin: 8pt 0 4pt; }
.page-break { break-before: page; page-break-before: always; }
.cover { min-height: 22.7cm; display: flex; flex-direction: column; justify-content: center; text-align: center; break-after: page; page-break-after: always; }
.cover .kind { font-size: 14pt; font-weight: bold; margin-bottom: 18pt; }
.cover .title { font-size: 14pt; line-height: 1.3; font-weight: bold; margin-bottom: 35pt; }
.cover p { text-align: center; text-indent: 0; margin-bottom: 5pt; }
.figure { text-align: center; text-indent: 0; break-inside: avoid; page-break-inside: avoid; margin: 6pt 0 0; }
.figure img { max-width: 100%; max-height: 19.5cm; object-fit: contain; }
.caption { font-size: 10pt; font-style: italic; text-align: center; text-indent: 0; margin: 4pt 0 8pt; break-before: avoid; page-break-before: avoid; }
.report-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 9pt; line-height: 1.15; margin: 0 0 10pt; break-inside: auto; }
.report-table tr { break-inside: avoid; page-break-inside: avoid; }
.report-table th, .report-table td { border: 0.5pt solid #444; padding: 5pt 6pt; vertical-align: middle; overflow-wrap: anywhere; }
.report-table th { background: #dce6f1; text-align: center; font-weight: bold; }
.report-table td:first-child { text-align: center; }
.code { font-family: Consolas, monospace; font-size: 8.2pt; line-height: 1.2; white-space: pre-wrap; overflow-wrap: anywhere; background: #f5f5f5; padding: 8pt; margin: 4pt 0 8pt; }
.toc { font-size: 10.5pt; line-height: 1.2; }
.toc-row { display: flex; align-items: baseline; margin: 0 0 3pt; break-inside: avoid; }
.toc-row .dots { flex: 1; border-bottom: 1px dotted #555; margin: 0 5pt 3pt; }
.toc-page { min-width: 1cm; text-align: right; }
.toc-level-2 { padding-left: 0.7cm; }
.toc-level-3 { padding-left: 1.4cm; }
"""


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("docx")
    parser.add_argument("--out", required=True)
    parser.add_argument("--assets", required=True)
    parser.add_argument("--page-map")
    args = parser.parse_args()

    docx = Path(args.docx).resolve()
    output = Path(args.out).resolve()
    assets = Path(args.assets).resolve()
    assets.mkdir(parents=True, exist_ok=True)
    page_map = json.loads(Path(args.page_map).read_text("utf-8")) if args.page_map else {}

    doc = Document(docx)
    headings = []
    figures = []
    tables = []
    body = []
    cover_paras = []
    in_cover = True
    skip_generated_index = None
    image_no = 0
    table_no = 0

    for block in blocks(doc):
        if isinstance(block, Table):
            if in_cover:
                in_cover = False
                body.append('<div class="page-break"></div>')
            table_no += 1
            body.append(table_html(block))
            continue

        p = block
        text = p.text.strip()
        style = p.style.name if p.style else ""

        if in_cover:
            if style == "Heading 1":
                in_cover = False
                body.append('<section class="cover">')
                nonempty = [x for x in cover_paras if x]
                if nonempty:
                    body.append(f'<div class="kind">{esc(nonempty[0])}</div>')
                if len(nonempty) > 1:
                    body.append(f'<div class="title">{esc(nonempty[1])}</div>')
                for item in nonempty[2:]:
                    body.append(f"<p>{esc(item)}</p>")
                body.append("</section>")
            else:
                cover_paras.append(text)
                continue

        has_page_break = page_break(p)
        if style.lower().startswith("toc ") or text == "No table of contents entries found.":
            continue
        if skip_generated_index and style not in ("Heading 1",):
            if has_page_break:
                body.append('<div class="page-break"></div>')
            continue
        if style == "Heading 1":
            skip_generated_index = None

        if has_page_break:
            body.append('<div class="page-break"></div>')

        if style.startswith("Heading"):
            level = int(style.split()[-1])
            key = f"h-{len(headings)+1}"
            headings.append((key, text, level))
            body.append(f'<h{level} id="{key}">{esc(text)}</h{level}>')
            if text == "DAFTAR ISI":
                body.append('<div class="toc">[[TOC]]</div>')
                skip_generated_index = "toc"
            elif text == "DAFTAR GAMBAR":
                body.append('<div class="toc">[[FIGURES]]</div>')
                skip_generated_index = "figures"
            elif text == "DAFTAR TABEL":
                body.append('<div class="toc">[[TABLES]]</div>')
                skip_generated_index = "tables"
            continue

        if style == "Figure Caption":
            key = f"fig-{len(figures)+1}"
            figures.append((key, text))
            body.append(f'<p class="caption" id="{key}">{esc(text)}</p>')
            continue
        if style == "Table Caption":
            key = f"tbl-{len(tables)+1}"
            tables.append((key, text))
            body.append(f'<p class="caption" id="{key}">{esc(text)}</p>')
            continue

        rel = image_rel(p)
        if rel:
            image_no += 1
            part = doc.part.related_parts[rel]
            suffix = Path(part.partname).suffix or ".png"
            image_path = assets / f"image-{image_no:02d}{suffix}"
            image_path.write_bytes(part.blob)
            body.append(f'<p class="figure"><img src="{image_path.as_uri()}" alt="Gambar {image_no}"></p>')
            continue

        if not text:
            continue
        if style == "List Bullet":
            body.append(f'<p class="bullet">{esc(text)}</p>')
            continue

        classes = []
        if p.alignment == 1:
            classes.append("center")
        if not p.paragraph_format.first_line_indent or p.paragraph_format.first_line_indent == 0:
            classes.append("no-indent")
        shading = p._p.xpath("./w:pPr/w:shd")
        if shading:
            body.append(f'<pre class="code">{esc(text)}</pre>')
        else:
            body.append(f'<p class="{" ".join(classes)}">{esc(text)}</p>')

    toc_items = []
    for key, label, level in headings:
        if label in ("DAFTAR ISI", "DAFTAR GAMBAR", "DAFTAR TABEL"):
            continue
        display = f'<div class="toc-level-{level}">' if level > 1 else "<div>"
        display += list_html([(key, label)], page_map) + "</div>"
        toc_items.append(display)

    content = "\n".join(body)
    content = content.replace("[[TOC]]", "".join(toc_items))
    content = content.replace("[[FIGURES]]", list_html(figures, page_map))
    content = content.replace("[[TABLES]]", list_html(tables, page_map))

    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(
        "<!doctype html><html lang='id'><head><meta charset='utf-8'>"
        f"<title>{esc(doc.core_properties.title)}</title><style>{CSS}</style></head>"
        f"<body>{content}</body></html>",
        encoding="utf-8",
    )
    manifest = {
        "headings": [{"key": k, "text": t, "level": l} for k, t, l in headings],
        "figures": [{"key": k, "text": t} for k, t in figures],
        "tables": [{"key": k, "text": t} for k, t in tables],
    }
    output.with_suffix(".manifest.json").write_text(json.dumps(manifest, indent=2), "utf-8")
    print(f"HTML={output}")
    print(f"HEADINGS={len(headings)} FIGURES={len(figures)} TABLES={len(tables)}")


if __name__ == "__main__":
    main()
