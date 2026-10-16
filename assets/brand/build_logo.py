from fontTools.ttLib import TTFont
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.pens.boundsPen import BoundsPen
import os
G = "/usr/share/fonts/truetype/google-fonts/"
OUT = os.environ.get("OUT", ".")

def text_path(fontfile, text, size, x0, baseline, tracking=0):
    f = TTFont(G + fontfile); gs = f.getGlyphSet(); cmap = f.getBestCmap(); upm = f["head"].unitsPerEm
    s = size / upm; x = x0; parts = []
    for ch in text:
        g = cmap[ord(ch)]
        pen = SVGPathPen(gs)
        tp = TransformPen(pen, (s, 0, 0, -s, x, baseline))
        gs[g].draw(tp)
        parts.append(pen.getCommands())
        x += gs[g].width * s + tracking
    return " ".join(p for p in parts if p), x

# Mark: shield (trust) + check (verified) + gold sparkle (premium, happiness)
def mark(id, x=0, y=0, scale=1.0, cut="#FFFFFF"):
    return f'''<g transform="translate({x} {y}) scale({scale})">
  <defs>
    <linearGradient id="{id}-g" x1="12" y1="4" x2="52" y2="60" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#3B63F3"/><stop offset="1" stop-color="#1B2A80"/></linearGradient>
    <linearGradient id="{id}-s" x1="44" y1="2" x2="60" y2="20" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#FFD66B"/><stop offset="1" stop-color="#F2A516"/></linearGradient>
  </defs>
  <path d="M32 4.5 L51.5 11.2 C53.4 11.9 54.6 13.6 54.6 15.6 V29.5 C54.6 44.2 45.3 54.6 32 59.6 C18.7 54.6 9.4 44.2 9.4 29.5 V15.6 C9.4 13.6 10.6 11.9 12.5 11.2 Z" fill="url(#{id}-g)"/>
  <path d="M32 9.2 L49.6 15.2 V29.5 C49.6 33 49 36.2 47.9 39.1 C43 30 34 24.5 15 24 V15.2 Z" fill="#FFFFFF" opacity="0.08"/>
  <path d="M20.5 32.5 L28.6 40.6 L44.5 24.2" fill="none" stroke="#FFFFFF" stroke-width="5.6" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M5.2 47.5 C-1.5 33 4.5 14.5 22 6.6 C31 2.6 40.5 2.6 47 5.4" fill="none" stroke="url(#{id}-s)" stroke-width="2.6" stroke-linecap="round" stroke-dasharray="0.01 5.2"/>
  <path d="M52 1.5 C52.9 6.9 54.9 8.9 60.3 9.8 C54.9 10.7 52.9 12.7 52 18.1 C51.1 12.7 49.1 10.7 43.7 9.8 C49.1 8.9 51.1 6.9 52 1.5 Z" fill="url(#{id}-s)" stroke="{cut}" stroke-width="1.8" stroke-linejoin="round"/>
</g>'''

def lockup(dark_bg=False):
    ink = "#FFFFFF" if dark_bg else "#0E1A47"
    blue = "#9DB4FF" if dark_bg else "#2747D4"
    gold = "#F6B93B" if dark_bg else "#E29B12"
    p1, x = text_path("Poppins-Bold.ttf", "Immigration", 30, 76, 42, -0.3)
    p2, x2 = text_path("Poppins-Bold.ttf", "CRM", 30, x + 1, 42, -0.3)
    p3, x3 = text_path("Poppins-Medium.ttf", ".shop", 17, x2 + 2, 42, 0.2)
    w = int(x3 + 4)
    return w, f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w} 64" width="{w}" height="64" role="img" aria-label="ImmigrationCRM.shop">
{mark("m" + ("d" if dark_bg else "l"), cut=("#0E1A47" if dark_bg else "#FFFFFF"))}
<path d="{p1}" fill="{ink}"/><path d="{p2}" fill="{blue}"/><path d="{p3}" fill="{gold}"/>
</svg>'''

w, light = lockup(False)
w2, dark = lockup(True)
open(f"{OUT}/logo.svg", "w").write(light)
open(f"{OUT}/logo-white.svg", "w").write(dark)
open(f"{OUT}/logo-mark.svg", "w").write(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64" role="img" aria-label="ImmigrationCRM.shop">{mark("mk", cut="#FFFFFF")}</svg>')
# favicon: mark on transparent, slightly zoomed
open(f"{OUT}/icon.svg", "w").write(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="2 0 62 64">{mark("fi")}</svg>')
print(w, w2)
