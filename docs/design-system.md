# Design System: Retro Pixel Art Aesthetic

DimsElektro visual language — GameBoy-inspired, press-start typography, sharp shadows, tactile buttons.

## Design Philosophy

**Retro accessibility**: GameBoy green + amber accents + cream background. Sharp borders, no rounded corners (except rare curves). Pixel-art sprites in hero, hand-drawn feel. Every interaction tactile (press, lift, feedback shadow).

No animations except necessary: dropdown fade, hover lift. No gradients or glass morphism.

## Color Palette

All colors in OKLch (perceptually uniform):

```
Brand (GameBoy Green):
  brand-50:   oklch(0.979 0.021 166.113)  — very light mint
  brand-100:  oklch(0.95 0.052 163.051)   — light mint
  brand-200:  oklch(0.905 0.093 164.15)   — pale green
  brand-300:  oklch(0.845 0.143 164.978)  — soft green
  brand-400:  oklch(0.765 0.177 163.223)  — mid green
  brand-500:  oklch(0.696 0.17 162.48)    — PRIMARY: action buttons, badges
  brand-600:  oklch(0.596 0.145 163.225)  — darker green (hover)
  brand-700:  oklch(0.508 0.118 165.612)  — dark green (links)
  brand-800:  oklch(0.432 0.095 166.913)  — very dark
  brand-900:  oklch(0.378 0.077 168.94)   — near black
  brand-950:  oklch(0.262 0.051 172.552)  — almost black

Neutrals (zinc):
  zinc-50:    white-ish
  zinc-900:   charcoal (borders, text)
  zinc-950:   near black (strong text)

Cream (canvas):
  --color-cream: #f7f4ef  — soft background

Semantic:
  red-500:    error buttons
  red-400:    error hover
  amber-300:  accent highlight (hero tag, price tag rotation)
  amber-50:   table row hover
  amber-100:  button hover
```

## Typography

```
Font Stack:
  sans:   'Inter', ui-sans-serif, system-ui (smooth body text)
  pixel:  'Press Start 2P', ui-monospace (retro labels, kickers)

Press Start 2P:
  - Used for: page titles (h1), section overlines (.overline-label), nav labels
  - Size: text-[10px] to text-[14px] in pixel scale
  - Line height: relaxed (looser than normal)
  - Letter spacing: tracking-wide

Inter:
  - Used for: body text (p), labels, button labels, tables
  - Weight: 400 (regular), 600 (semibold), 700 (bold), 900 (extrabold)
  - Size: text-xs (12px) to text-lg (18px)
```

## Components

### Buttons

All buttons: sharp borders, 3px black shadow (lifted), no radius.

```html
<!-- Primary (brand green) -->
<button class="btn btn-primary">Action</button>
<!-- Output: GameBoy green bg, black text, 3px shadow, hover lift -->

<!-- Secondary (white) -->
<button class="btn btn-secondary">Secondary</button>

<!-- Danger (red) -->
<button class="btn btn-danger">Delete</button>

<!-- Dark (charcoal) -->
<button class="btn btn-dark">Dark</button>

<!-- Small -->
<button class="btn btn-sm">Kecil</button>

<!-- Icon button (cart, menu) -->
<button class="icon-btn">🛒</button>
<!-- 40x40 square, grid center, same lifted shadow -->
```

Interactions:
- **Hover**: translate up 2px, shadow lifted 4px (tactile lift)
- **Active**: translate down 2px, shadow collapsed (press feel)
- **Focus**: ring-2 offset-2 around button
- **Disabled**: opacity-50, no hover effect

### Forms

```html
<!-- Text input -->
<input type="text" class="input" />
<!-- border-2 black, white bg, 3px shadow, focus amber-50 bg -->

<!-- Search input (slimmer) -->
<input type="text" class="input-search" />
<!-- icon-left variant, no shadow -->

<!-- Error state -->
<input type="text" class="input input-error" />
<!-- red-600 border, red-50 bg -->

<!-- Checkbox -->
<input type="checkbox" class="checkbox" />
<!-- 4x4 square, black border-2, accent brand-600 -->

<!-- Label -->
<label class="label">Field name</label>
<!-- text-sm font-bold, zinc-900 -->
```

### Cards / Surfaces

```html
<!-- Default card -->
<div class="card">Content</div>
<!-- border-2 black, white bg, 4px shadow -->

<!-- Interactive card (hover lift) -->
<div class="card card-hover">Clickable</div>
<!-- hover: translate up 1px, shadow lift to 6px -->

<!-- Badge -->
<span class="badge">Stok Menipis</span>
<!-- inline-flex, border-2 black, bg varies, text-[11px] bold uppercase -->

<!-- Price tag (rotated -2°) -->
<span class="price-tag">Rp 1.299.000</span>
<!-- amber-300 bg, rotate-[-2deg], shadow-[2px_2px_0px], inline-block -->
```

### Navigation

```html
<!-- Category nav (amber rail under header) -->
<a href="#" class="nav-link">Laptop</a>
<!-- border-2 transparent, hover border-2 black bg-white -->

<!-- Active category -->
<a href="#" class="nav-link nav-link-active">Monitor</a>
<!-- border-2 black, bg-zinc-900 (dark), text-white -->

<!-- Admin sidebar link -->
<a href="#" class="admin-link">Dashboard</a>
<!-- flex gap-2.5, border-2 transparent, text-zinc-600 -->

<!-- Admin link active -->
<a href="#" class="admin-link admin-link-active">Orders</a>
<!-- border-2 black, bg-brand-500, text-zinc-950, 3px shadow -->

<!-- Plain link (underline) -->
<a href="#" class="link">Lihat semua →</a>
<!-- font-bold, text-brand-700, underline decoration-2 underline-offset-2 -->
```

### Tables

```html
<table>
  <thead>
    <tr>
      <th class="table-th">Column</th>
    </tr>
  </thead>
  <tbody>
    <tr class="table-row-hover">
      <td class="table-td">Data</td>
    </tr>
  </tbody>
</table>
<!-- th: text-[11px] uppercase tracking-wide, text-zinc-500 -->
<!-- td: text-sm text-zinc-700, padding px-4 py-3 -->
<!-- row hover: transition bg-amber-50 -->
```

### Dropdown

```html
<div class="dropdown-panel">
  <a href="#" class="dropdown-item">Option 1</a>
  <a href="#" class="dropdown-item">Option 2</a>
</div>
<!-- panel: absolute z-30, border-2 black, bg-white, 4px shadow -->
<!-- item: block px-4 py-2 text-sm font-medium, hover bg-amber-100 -->
<!-- animation: dropdown-in 0.15s steps(4, end) — 4-frame pixel drop -->
```

### Sections

```html
<!-- Section title -->
<p class="overline-label">// KATEGORI</p>  <!-- pixel font, [10px], text-brand-700 -->
<h2 class="section-title">Belanja per kategori</h2>  <!-- text-xl font-extrabold -->

<!-- Grid: products -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
  <!-- product cards -->
</div>

<!-- Grid: categories -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
  <!-- category cards -->
</div>
```

## Tailwind Config

Defined in `resources/css/app.css` @theme block:

```css
--font-sans: 'Inter', ...
--font-pixel: 'Press Start 2P', ...
--color-cream: #f7f4ef
--color-brand-50 through brand-950: OKLch values
```

All component utilities (`.btn`, `.card`, `.input`, etc.) in `@layer components`.

## Spacing & Layout

```
Gap/padding scale: 1 (4px), 2 (8px), 4 (16px), 6 (24px), 8 (32px), 12 (48px)
Border width: 2px (sharp pixel line)
Shadow: [3px_3px_0px_0px_rgba(0,0,0,1)] (lifted), [6px_6px_0px_0px_...] (big lift)
Radius: none (all sharp corners, no rounded)
```

## Hero Section

Gradient-free. Flat amber-300 background with:
- 2px black border all sides
- 6px black shadow (strong lift)
- Horizontal line pattern (repeating-linear-gradient, subtle)
- SVG pixel-art graphics (monitor, handheld, phone)
- Press Start 2P kicker "GRATIS ONGKIR · GARANSI RESMI" in tiny pixels
- Text: black on cream/amber, bold tracking-tight

## Responsive

Mobile-first breakpoints:
- `sm:` 640px — tablet
- `lg:` 1024px — desktop
- `xl:` 1280px — large desktop

Grid defaults: 1 col mobile, 2-3 cols tablet, 4-5 cols desktop.

## Accessibility

- All buttons keyboard-focusable (ring-2 focus-visible)
- Links underlined (not just color)
- Form errors: red border + red-50 bg + error text
- Alt text on hero SVG (`aria-hidden="true"`)
- Focus order logical (left-to-right, top-to-bottom)
- Color not only signal (always text labels too)
- No motion-triggered animations (only on user hover/click)

## Fonts

Two Google Fonts required:

1. **Inter** (hosted, weights 400/600/700/900)
2. **Press Start 2P** (hosted, weight 400 only)

Both loaded via CSS @import in `resources/css/app.css`.
