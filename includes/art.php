<?php
/* Product pictures.
   1. If the product has an uploaded photo (products.image), that photo is shown.
   2. Otherwise a clean illustration is drawn from the product type (products.art)
      and its colour (products.swatch). No image files are needed for the demo data. */

function hex_mix(string $a, string $b, float $t): string {
    $a = ltrim($a, '#');
    $b = ltrim($b, '#');
    $out = '#';
    for ($i = 0; $i < 6; $i += 2) {
        $x = hexdec(substr($a, $i, 2));
        $y = hexdec(substr($b, $i, 2));
        $out .= str_pad(dechex((int) round($x + ($y - $x) * $t)), 2, '0', STR_PAD_LEFT);
    }
    return $out;
}

function art_types(): array {
    return [
        'headphones' => 'Headphones', 'watch' => 'Watch', 'speaker' => 'Speaker', 'hub' => 'Charging hub',
        'keyboard' => 'Keyboard', 'jacket' => 'Jacket', 'sneakers' => 'Sneakers', 'scarf' => 'Scarf',
        'belt' => 'Belt', 'backpack' => 'Backpack', 'sunglasses' => 'Sunglasses', 'tote' => 'Tote bag',
        'mugs' => 'Mugs', 'lamp' => 'Lamp', 'blanket' => 'Blanket', 'candles' => 'Candles',
        'bottle' => 'Bottle', 'plant' => 'Plant', 'book' => 'Book', 'notebook' => 'Notebook',
    ];
}

function product_image(array $p, string $base = '', string $class = 'pimg'): string {
    if (!empty($p['image']) && is_file(__DIR__ . '/../assets/img/products/' . $p['image'])) {
        return '<img class="' . $class . '" src="' . htmlspecialchars($base) . 'assets/img/products/' . htmlspecialchars($p['image'])
            . '" alt="' . htmlspecialchars($p['name']) . '" loading="lazy">';
    }
    return product_art($p, $class);
}

function product_art(array $p, string $class = 'pimg'): string {
    static $n = 0;
    $n++;
    $c = preg_match('/^#[0-9a-fA-F]{6}$/', $p['swatch'] ?? '') ? $p['swatch'] : '#3B4A6B';
    $d = hex_mix($c, '#000000', 0.32);
    $l = hex_mix($c, '#ffffff', 0.45);
    $bg = hex_mix($c, '#ffffff', 0.86);
    $bg2 = hex_mix($c, '#ffffff', 0.72);
    $ink = '#18203A';
    $type = $p['art'] ?? 'book';
    $variant = (int) ($p['id'] ?? 0) % 3;
    $uid = 'a' . $n;
    $s = '';

    switch ($type) {
        case 'headphones':
            $s = "<path d='M112 238 C108 96 292 96 288 238' fill='none' stroke='$d' stroke-width='18' stroke-linecap='round'/>"
               . "<path d='M126 226 C126 120 274 120 274 226' fill='none' stroke='$l' stroke-width='5' stroke-linecap='round' opacity='.7'/>"
               . "<rect x='84' y='206' width='54' height='104' rx='24' fill='$c'/><rect x='262' y='206' width='54' height='104' rx='24' fill='$c'/>"
               . "<rect x='96' y='222' width='30' height='72' rx='15' fill='$d'/><rect x='274' y='222' width='30' height='72' rx='15' fill='$d'/>"
               . "<circle cx='111' cy='258' r='9' fill='$l'/><circle cx='289' cy='258' r='9' fill='$l'/>";
            break;
        case 'watch':
            $s = "<rect x='164' y='34' width='72' height='112' rx='16' fill='$d'/><rect x='164' y='254' width='72' height='112' rx='16' fill='$d'/>"
               . "<rect x='140' y='116' width='120' height='168' rx='38' fill='$c'/><rect x='254' y='170' width='10' height='34' rx='4' fill='$d'/>"
               . "<rect x='152' y='128' width='96' height='144' rx='28' fill='#0F172A'/>"
               . "<circle cx='200' cy='200' r='34' fill='none' stroke='#2A3550' stroke-width='9'/>"
               . "<path d='M200 166 A34 34 0 1 1 168 214' fill='none' stroke='$l' stroke-width='9' stroke-linecap='round'/>"
               . "<text x='200' y='207' text-anchor='middle' font-family='Arial,sans-serif' font-size='20' font-weight='700' fill='#fff'>10:09</text>"
               . "<circle cx='176' cy='146' r='4' fill='#FF5B45'/>";
            break;
        case 'speaker':
            $dots = '';
            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $dots .= "<circle cx='" . (146 + $x * 15.5) . "' cy='" . (146 + $y * 15.5) . "' r='3.6' fill='$d' opacity='.55'/>";
                }
            }
            $s = "<path d='M160 108 C160 70 240 70 240 108' fill='none' stroke='$d' stroke-width='9' stroke-linecap='round'/>"
               . "<rect x='116' y='104' width='168' height='206' rx='50' fill='$c'/>"
               . "<rect x='132' y='126' width='136' height='136' rx='30' fill='$d' opacity='.25'/>$dots"
               . "<circle cx='176' cy='288' r='8' fill='$l'/><circle cx='200' cy='288' r='8' fill='$l'/><circle cx='224' cy='288' r='8' fill='$l'/>";
            break;
        case 'hub':
            $s = "<path d='M330 200 C370 200 372 300 330 316 L250 340' fill='none' stroke='$d' stroke-width='10' stroke-linecap='round'/>"
               . "<rect x='62' y='158' width='274' height='84' rx='22' fill='$c'/><rect x='62' y='158' width='274' height='26' rx='13' fill='$l' opacity='.5'/>"
               . "<rect x='84' y='198' width='56' height='22' rx='4' fill='#0F172A'/><rect x='152' y='198' width='34' height='22' rx='4' fill='#0F172A'/>"
               . "<rect x='198' y='198' width='34' height='22' rx='4' fill='#0F172A'/><rect x='244' y='198' width='34' height='22' rx='4' fill='#0F172A'/>"
               . "<rect x='290' y='202' width='30' height='14' rx='7' fill='#0F172A'/><circle cx='84' cy='172' r='3' fill='#12A594'/>";
            break;
        case 'keyboard':
            $keys = '';
            for ($r = 0; $r < 4; $r++) {
                for ($k = 0; $k < 10; $k++) {
                    $w = ($r === 3 && $k >= 2 && $k <= 6) ? 0 : 22;
                    if ($r === 3 && $k >= 2 && $k <= 6) { continue; }
                    $keys .= "<rect x='" . (66 + $k * 27) . "' y='" . (140 + $r * 28) . "' width='22' height='22' rx='5' fill='" . ($k % 5 === 0 && $r === 0 ? $l : '#fff') . "'/>";
                }
            }
            $keys .= "<rect x='147' y='224' width='130' height='22' rx='5' fill='#fff'/>";
            $s = "<rect x='50' y='118' width='300' height='150' rx='20' fill='$d'/><rect x='50' y='112' width='300' height='150' rx='20' fill='$c'/>$keys";
            break;
        case 'jacket':
            $s = "<path d='M128 104 L172 82 L228 82 L272 104 L338 162 L318 290 L282 274 L282 336 L118 336 L118 274 L82 290 L62 162 Z' fill='$c'/>"
               . "<path d='M172 82 L200 128 L228 82 L214 78 L200 98 L186 78 Z' fill='$l'/>"
               . "<path d='M200 98 L200 336' stroke='$d' stroke-width='4'/>"
               . "<rect x='140' y='196' width='46' height='44' rx='4' fill='none' stroke='$d' stroke-width='3' stroke-dasharray='6 4'/>"
               . "<rect x='214' y='196' width='46' height='44' rx='4' fill='none' stroke='$d' stroke-width='3' stroke-dasharray='6 4'/>"
               . "<circle cx='193' cy='140' r='4' fill='$l'/><circle cx='207' cy='180' r='4' fill='$l'/><circle cx='193' cy='260' r='4' fill='$l'/><circle cx='207' cy='300' r='4' fill='$l'/>"
               . "<path d='M118 274 L282 274' stroke='$d' stroke-width='3' stroke-dasharray='6 4'/>";
            break;
        case 'sneakers':
            $s = "<path d='M62 262 C60 214 96 196 132 202 L170 150 C194 132 232 150 252 184 C296 192 344 214 348 258 L348 290 L62 290 Z' fill='$c'/>"
               . "<path d='M252 184 C296 192 344 214 348 258 L292 258 C290 226 272 200 252 184 Z' fill='$l' opacity='.55'/>"
               . "<rect x='54' y='278' width='302' height='30' rx='15' fill='#F4F4F2' stroke='$d' stroke-width='3'/>"
               . "<path d='M176 160 L206 186 M192 150 L222 176 M208 150 L236 176' stroke='#fff' stroke-width='7' stroke-linecap='round'/>"
               . "<path d='M96 252 L300 252' stroke='$d' stroke-width='5' opacity='.5'/><circle cx='118' cy='224' r='6' fill='$d' opacity='.5'/>";
            break;
        case 'scarf':
            $s = "<ellipse cx='200' cy='112' rx='94' ry='42' fill='$c'/><ellipse cx='200' cy='112' rx='94' ry='42' fill='none' stroke='$d' stroke-width='4'/>"
               . "<ellipse cx='200' cy='118' rx='54' ry='20' fill='$bg2'/>"
               . "<path d='M146 138 L214 152 L252 322 L186 332 Z' fill='$c'/><path d='M214 150 L276 140 L304 306 L252 322 Z' fill='$d'/>"
               . "<path d='M164 214 L232 228 M174 258 L242 272 M184 298 L250 310' stroke='$l' stroke-width='9'/>"
               . "<path d='M262 206 L292 200 M270 252 L298 246' stroke='$l' stroke-width='8'/>"
               . "<path d='M190 332 l-3 22 M202 330 l-3 24 M214 328 l-3 24 M226 326 l-3 24 M238 324 l-3 24 M254 322 l-1 22 M268 320 l-1 22 M282 318 l-1 22 M296 316 l-1 22' stroke='$d' stroke-width='5' stroke-linecap='round'/>";
            break;
        case 'belt':
            $holes = '';
            for ($i = 0; $i < 6; $i++) {
                $a = deg2rad(200 + $i * 24);
                $holes .= "<circle cx='" . round(200 + 100 * cos($a)) . "' cy='" . round(206 + 100 * sin($a)) . "' r='4' fill='$bg'/>";
            }
            $s = "<circle cx='200' cy='206' r='100' fill='none' stroke='$d' stroke-width='46'/><circle cx='200' cy='206' r='100' fill='none' stroke='$c' stroke-width='38'/>"
               . "<circle cx='200' cy='206' r='100' fill='none' stroke='$l' stroke-width='3' stroke-dasharray='8 6' opacity='.8'/>$holes"
               . "<rect x='268' y='88' width='62' height='62' rx='10' fill='none' stroke='#C9CED6' stroke-width='11'/><rect x='292' y='96' width='9' height='46' fill='#C9CED6'/>";
            break;
        case 'backpack':
            $s = "<path d='M160 96 C160 56 240 56 240 96' fill='none' stroke='$d' stroke-width='12' stroke-linecap='round'/>"
               . "<rect x='108' y='86' width='184' height='254' rx='56' fill='$c'/>"
               . "<rect x='132' y='198' width='136' height='112' rx='26' fill='$d' opacity='.35'/><rect x='132' y='192' width='136' height='112' rx='26' fill='$l' opacity='.55'/>"
               . "<path d='M150 232 L250 232' stroke='$d' stroke-width='5' stroke-linecap='round'/><rect x='190' y='222' width='20' height='22' rx='6' fill='$d'/>"
               . "<path d='M140 150 C170 130 230 130 260 150' fill='none' stroke='$d' stroke-width='6' stroke-linecap='round'/>"
               . "<rect x='86' y='170' width='22' height='90' rx='10' fill='$d'/><rect x='292' y='170' width='22' height='90' rx='10' fill='$d'/>";
            break;
        case 'sunglasses':
            $s = "<path d='M70 190 L48 170 M330 190 L352 170' stroke='$d' stroke-width='10' stroke-linecap='round'/>"
               . "<circle cx='132' cy='214' r='62' fill='#111827'/><circle cx='268' cy='214' r='62' fill='#111827'/>"
               . "<circle cx='132' cy='214' r='62' fill='none' stroke='$c' stroke-width='12'/><circle cx='268' cy='214' r='62' fill='none' stroke='$c' stroke-width='12'/>"
               . "<path d='M194 204 C200 190 200 190 206 204' fill='none' stroke='$c' stroke-width='10' stroke-linecap='round'/>"
               . "<path d='M100 196 C108 180 124 176 140 178' fill='none' stroke='#fff' stroke-width='6' stroke-linecap='round' opacity='.45'/>"
               . "<path d='M236 196 C244 180 260 176 276 178' fill='none' stroke='#fff' stroke-width='6' stroke-linecap='round' opacity='.45'/>";
            break;
        case 'tote':
            $s = "<path d='M148 130 C148 40 252 40 252 130' fill='none' stroke='$d' stroke-width='12' stroke-linecap='round'/>"
               . "<path d='M96 128 L304 128 L324 338 L76 338 Z' fill='$c'/><path d='M96 128 L304 128 L308 160 L92 160 Z' fill='$l' opacity='.5'/>"
               . "<circle cx='200' cy='244' r='44' fill='none' stroke='$d' stroke-width='6'/><path d='M178 252 L194 232 L204 246 L212 236 L224 252 Z' fill='$d'/>";
            break;
        case 'mugs':
            $s = "<rect x='168' y='126' width='108' height='116' rx='16' fill='$l'/><path d='M276 150 C318 150 318 214 276 214' fill='none' stroke='$l' stroke-width='14'/>"
               . "<rect x='168' y='126' width='108' height='22' rx='11' fill='$c' opacity='.35'/>"
               . "<rect x='92' y='176' width='116' height='128' rx='18' fill='$c'/><path d='M92 206 C48 206 48 270 92 270' fill='none' stroke='$c' stroke-width='14'/>"
               . "<rect x='92' y='176' width='116' height='24' rx='12' fill='$d' opacity='.35'/><rect x='108' y='236' width='84' height='10' rx='5' fill='#fff' opacity='.55'/>"
               . "<path d='M226 104 C216 90 238 82 228 66 M254 104 C244 90 266 82 256 66' fill='none' stroke='$d' stroke-width='5' stroke-linecap='round' opacity='.45'/>";
            break;
        case 'lamp':
            $s = "<ellipse cx='200' cy='248' rx='120' ry='70' fill='#FFD76A' opacity='.28'/>"
               . "<rect x='192' y='170' width='16' height='140' rx='6' fill='$d'/>"
               . "<rect x='140' y='300' width='120' height='28' rx='14' fill='#B08968'/><rect x='140' y='300' width='120' height='9' rx='4' fill='#C9A27E'/>"
               . "<path d='M126 98 L274 98 L306 190 L94 190 Z' fill='$c'/><path d='M126 98 L274 98 L280 116 L120 116 Z' fill='$l' opacity='.55'/>"
               . "<circle cx='200' cy='200' r='9' fill='#FFE08A'/>";
            break;
        case 'blanket':
            $stripes = '';
            for ($i = 0; $i < 6; $i++) {
                $stripes .= "<rect x='64' y='" . (174 + $i * 26) . "' width='272' height='10' fill='" . ($i % 2 ? $l : $d) . "' opacity='.55'/>";
            }
            $s = "<rect x='64' y='150' width='272' height='170' rx='12' fill='$c'/>$stripes"
               . "<rect x='64' y='120' width='272' height='52' rx='12' fill='$l'/><path d='M64 148 C130 164 270 164 336 148' fill='none' stroke='$d' stroke-width='3' opacity='.4'/>"
               . "<path d='M84 320 l0 22 M104 320 l0 22 M124 320 l0 22 M144 320 l0 22 M164 320 l0 22 M184 320 l0 22 M204 320 l0 22 M224 320 l0 22 M244 320 l0 22 M264 320 l0 22 M284 320 l0 22 M304 320 l0 22 M324 320 l0 22' stroke='$d' stroke-width='4' stroke-linecap='round'/>";
            break;
        case 'candles':
            $s = "<g><rect x='64' y='196' width='84' height='118' rx='14' fill='$c'/><rect x='64' y='226' width='84' height='40' fill='#fff' opacity='.8'/><path d='M106 196 L106 176' stroke='$d' stroke-width='4'/><path d='M106 134 C92 158 92 176 106 176 C120 176 120 158 106 134 Z' fill='#FFB020'/></g>"
               . "<g><rect x='158' y='150' width='84' height='164' rx='14' fill='$l'/><rect x='158' y='200' width='84' height='60' fill='#fff' opacity='.8'/><path d='M200 150 L200 130' stroke='$d' stroke-width='4'/><path d='M200 88 C186 112 186 130 200 130 C214 130 214 112 200 88 Z' fill='#FFB020'/></g>"
               . "<g><rect x='252' y='222' width='84' height='92' rx='14' fill='$d'/><rect x='252' y='248' width='84' height='32' fill='#fff' opacity='.8'/><path d='M294 222 L294 202' stroke='$ink' stroke-width='4'/><path d='M294 160 C280 184 280 202 294 202 C308 202 308 184 294 160 Z' fill='#FFB020'/></g>";
            break;
        case 'bottle':
            $s = "<rect x='164' y='50' width='72' height='46' rx='12' fill='$d'/><rect x='176' y='94' width='48' height='22' fill='$l'/>"
               . "<path d='M168 116 L232 116 C262 130 262 160 262 176 L262 320 C262 340 248 350 228 350 L172 350 C152 350 138 340 138 320 L138 176 C138 160 138 130 168 116 Z' fill='$c'/>"
               . "<rect x='138' y='206' width='124' height='64' fill='#fff' opacity='.85'/><path d='M200 218 C190 234 190 244 200 250 C210 244 210 234 200 218 Z' fill='$d'/>"
               . "<path d='M158 140 L158 320' stroke='#fff' stroke-width='6' stroke-linecap='round' opacity='.35'/>";
            break;
        case 'plant':
            $s = "<path d='M200 270 C196 200 150 170 120 130 C170 140 196 190 200 270 Z' fill='#2E8B57'/>"
               . "<path d='M200 270 C204 190 250 150 292 120 C240 126 206 190 200 270 Z' fill='#3BA56A'/>"
               . "<path d='M200 270 C190 210 190 150 206 84 C226 150 214 210 200 270 Z' fill='#2E8B57'/>"
               . "<path d='M200 270 C150 250 100 226 78 196 C130 196 176 224 200 270 Z' fill='#3BA56A'/>"
               . "<path d='M200 270 C250 252 300 232 326 204 C276 200 226 226 200 270 Z' fill='#2E8B57'/>"
               . "<path d='M128 270 L272 270 L254 346 L146 346 Z' fill='$c'/><rect x='120' y='260' width='160' height='24' rx='8' fill='$l'/>";
            break;
        case 'notebook':
            $rings = '';
            for ($i = 0; $i < 7; $i++) {
                $rings .= "<rect x='" . (126 + $i * 24) . "' y='48' width='8' height='30' rx='4' fill='$ink'/>";
            }
            $s = "<rect x='108' y='64' width='184' height='272' rx='14' fill='$c'/>$rings"
               . "<rect x='108' y='64' width='184' height='36' rx='14' fill='$d' opacity='.35'/>"
               . "<rect x='132' y='140' width='136' height='74' rx='8' fill='#fff' opacity='.9'/><path d='M148 164 L252 164 M148 184 L226 184' stroke='$d' stroke-width='6' stroke-linecap='round'/>"
               . "<rect x='262' y='64' width='14' height='272' fill='#FFC93C'/>";
            break;
        case 'book':
        default:
            $motif = '';
            if ($variant === 0) {
                $motif = "<circle cx='200' cy='150' r='46' fill='none' stroke='#fff' stroke-width='8' opacity='.85'/><circle cx='200' cy='150' r='20' fill='#fff' opacity='.85'/>";
            } elseif ($variant === 1) {
                $motif = "<path d='M146 110 L254 110 M146 134 L254 134 M146 158 L254 158 M146 182 L254 182' stroke='#fff' stroke-width='9' stroke-linecap='round' opacity='.85'/>";
            } else {
                $motif = "<path d='M200 100 L250 190 L150 190 Z' fill='#fff' opacity='.88'/><circle cx='200' cy='150' r='9' fill='$c'/>";
            }
            $s = "<rect x='104' y='52' width='196' height='292' rx='8' fill='#fff' stroke='$d' stroke-width='3'/>"
               . "<rect x='98' y='46' width='196' height='292' rx='8' fill='$c'/><rect x='98' y='46' width='20' height='292' rx='6' fill='$d' opacity='.45'/>"
               . "$motif<rect x='142' y='250' width='112' height='10' rx='5' fill='#fff' opacity='.9'/><rect x='162' y='272' width='72' height='8' rx='4' fill='#fff' opacity='.6'/>";
            break;
    }

    return "<svg class='$class' viewBox='0 0 400 400' role='img' aria-label='" . htmlspecialchars($p['name'] ?? 'Product') . "' xmlns='http://www.w3.org/2000/svg'>"
        . "<defs><linearGradient id='g$uid' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='$bg'/><stop offset='1' stop-color='$bg2'/></linearGradient></defs>"
        . "<rect width='400' height='400' fill='url(#g$uid)'/>"
        . "<ellipse cx='200' cy='356' rx='" . ($type === 'blanket' ? 150 : 118) . "' ry='12' fill='$d' opacity='.14'/>"
        . $s . '</svg>';
}
