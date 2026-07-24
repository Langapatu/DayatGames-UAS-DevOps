from pathlib import Path
import sys

from docx import Document
from docx.enum.text import WD_ALIGN_PARAGRAPH


path = Path(sys.argv[1]).resolve()
doc = Document(path)
fixed = 0
for paragraph in doc.paragraphs:
    if paragraph._p.xpath("./w:pPr/w:shd"):
        paragraph.alignment = WD_ALIGN_PARAGRAPH.LEFT
        fixed += 1
doc.save(path)
print(f"FIXED_CODE_PARAGRAPHS={fixed}")
