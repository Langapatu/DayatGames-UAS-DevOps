import { mkdir, readdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

const sourceDirectory = process.argv[2];
const outputDirectory = process.argv[3];

if (!sourceDirectory || !outputDirectory) {
    throw new Error('Usage: node scripts/inspect-assets.mjs <source-directory> <output-directory>');
}

await mkdir(outputDirectory, { recursive: true });

const files = (await readdir(sourceDirectory, { withFileTypes: true }))
    .filter((entry) => entry.isFile())
    .map((entry) => entry.name)
    .sort();

const metadata = [];

for (const [index, file] of files.entries()) {
    const sourcePath = path.join(sourceDirectory, file);
    const image = sharp(sourcePath, { failOn: 'none' });
    const info = await image.metadata();
    const previewName = `${String(index + 1).padStart(2, '0')}-${path.parse(file).name}.png`;

    await image
        .clone()
        .resize({ width: 480, height: 640, fit: 'inside', withoutEnlargement: true })
        .png()
        .toFile(path.join(outputDirectory, previewName));

    metadata.push({
        source: file,
        width: info.width,
        height: info.height,
        format: info.format,
        preview: previewName,
    });
}

await writeFile(
    path.join(outputDirectory, 'metadata.json'),
    `${JSON.stringify(metadata, null, 2)}\n`,
    'utf8',
);

console.log(JSON.stringify(metadata, null, 2));
