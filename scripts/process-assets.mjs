import { mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

const assetRoot = process.argv[2];
const projectRoot = process.argv[3] ?? process.cwd();

if (!assetRoot) {
    throw new Error('Usage: node scripts/process-assets.mjs <uas-asset-root> [project-root]');
}

const mapping = [
    ['7aFAQ28D5avzogf7TFg11ryH.jpg', 'death-stranding-directors-cut.webp', 'Death Stranding Director’s Cut'],
    ['9e09677388a2f815ab02fe08cc918d945b822d4041a55023.jpg', 'forza-horizon-6.webp', 'Forza Horizon 6'],
    ['Atomic_Heart_cover.jpg', 'atomic-heart.webp', 'Atomic Heart'],
    ['EGS_GodofWar_SantaMonicaStudio_S2_1200x1600-fbdf3cbc2980749091d52751ffabb7b7_1200x1600-fbdf3cbc2980749091d52751ffabb7b7.jpg', 'god-of-war.webp', 'God of War'],
    ['Ghost_of_Tsushima.jpg', 'ghost-of-tsushima.webp', 'Ghost of Tsushima'],
    ['It_Takes_Two_cover_art.jpg', 'it-takes-two.webp', 'It Takes Two'],
    ['K6mmm89oNII1iI1aqaClO0wh.jpg', 'grand-theft-auto-v.webp', 'Grand Theft Auto V'],
    ['Kingdom_Come_Deliverance_II.jpg', 'kingdom-come-deliverance-ii.webp', 'Kingdom Come: Deliverance II'],
    ['Mafia_The_Old_Country_cover_art.jpg', 'mafia-the-old-country.webp', 'Mafia: The Old Country'],
    ['RDR2.jpg', 'red-dead-redemption-2.webp', 'Red Dead Redemption 2'],
    ['Road_96_cover.jpg', 'road-96.webp', 'Road 96'],
    ['Sons_of_the_Forest.jpg', 'sons-of-the-forest.webp', 'Sons of the Forest'],
    ['co2228_7_.jpg', 'detroit-become-human.webp', 'Detroit: Become Human'],
    ['d0afe358ec7a75f2f37a34194ccea4bf2d1a77029d6c8ff9.jpg', 'ghost-of-yotei.webp', 'Ghost of Yōtei'],
    ['dinner-1hsix.jpg', 'lego-batman-legacy-of-the-dark-knight.webp', 'LEGO Batman: Legacy of the Dark Knight'],
    ['f6b1e4512ee6061913f7d604da8f5f39566be56ca32a68ee.jpg', 'hogwarts-legacy.webp', 'Hogwarts Legacy'],
    ['images.jpg', 'subnautica-2.webp', 'Subnautica 2'],
];

const sourceGames = path.join(assetRoot, 'ImageGames');
const sourceLogo = path.join(assetRoot, 'Logo', 'DayatGames.png');
const gamesOutput = path.join(projectRoot, 'public', 'images', 'games');
const brandOutput = path.join(projectRoot, 'public', 'images', 'brand');

await mkdir(gamesOutput, { recursive: true });
await mkdir(brandOutput, { recursive: true });

const manifest = [];

for (const [sourceName, outputName, game] of mapping) {
    const input = path.join(sourceGames, sourceName);
    const sourceMetadata = await sharp(input, { failOn: 'none' }).metadata();
    const outputPath = path.join(gamesOutput, outputName);
    const result = await sharp(input, { failOn: 'none' })
        .rotate()
        .resize({ width: 1200, height: 1600, fit: 'inside', withoutEnlargement: true })
        .webp({ quality: 86, effort: 5 })
        .toFile(outputPath);

    manifest.push({
        source: sourceName,
        source_format: sourceMetadata.format,
        source_width: sourceMetadata.width,
        source_height: sourceMetadata.height,
        game,
        output: outputName,
        output_width: result.width,
        output_height: result.height,
        use: 'cover',
    });
}

await sharp(sourceLogo).png({ compressionLevel: 9 }).toFile(path.join(brandOutput, 'dayatgames-logo.png'));
await sharp(sourceLogo).resize(64, 64, { fit: 'contain' }).png().toFile(path.join(projectRoot, 'public', 'favicon.png'));
await writeFile(
    path.join(projectRoot, 'storage', 'app', 'asset-manifest.json'),
    `${JSON.stringify(manifest, null, 2)}\n`,
    'utf8',
);

console.log(`Processed ${manifest.length} game images and the supplied DayatGames logo.`);
