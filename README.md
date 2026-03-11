# Tattoo Aftercare Instructions

A WordPress plugin that displays an interactive aftercare timeline for tattoo studios. Clients pick their tattoo date and get personalized healing phase tracking with step-by-step care instructions.

![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue?logo=wordpress)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple?logo=php)
![License](https://img.shields.io/badge/License-GPL--2.0--or--later-green)
![Version](https://img.shields.io/badge/Version-1.0.0-gold)

## Features

- **5-phase healing timeline** — First Hours, Days 1-3, Days 4-14, Weeks 2-4, Long-Term
- **Personalized day tracker** — client picks their tattoo date, sees "Day X of healing" with their current phase highlighted
- **Progress visualization** — completed phases get green checkmarks, current phase glows with accent color
- **Remembers the date** — uses localStorage so clients don't re-enter their tattoo date on each visit
- **Print-friendly** — one click opens all phases and triggers clean print layout
- **Emergency warning** — configurable infection warning at the bottom
- **Studio contact card** — phone and email so clients can reach you
- **Fully customizable** — edit phases, instructions, colors, and studio info from the admin
- **Dark-themed & responsive** — looks great on all devices
- **Simple shortcode** — `[tattoo_aftercare]`

## How It Works

1. Client visits your aftercare page
2. Enters the date they got their tattoo
3. The timeline highlights their **current healing phase** and marks completed phases
4. They can expand any phase to read detailed care instructions
5. One click to print a clean copy to take home

## Installation

1. Download the latest release zip
2. Go to **Plugins > Add New > Upload Plugin** in WordPress admin
3. Upload the zip and activate
4. Configure at **Settings > Tattoo Aftercare**
5. Add `[tattoo_aftercare]` to any page

## Configuration

All settings live in **Settings > Tattoo Aftercare**:

| Section | What You Can Configure |
|---------|----------------------|
| Studio Info | Studio name, phone/WhatsApp, email, emergency note |
| Display | Day tracker toggle, print button toggle, accent color |
| Phases | Title, emoji icon, day range, and instructions (one per line) |

### Default Phases

| Phase | Days | What It Covers |
|-------|------|---------------|
| First 2-4 Hours | Day 0 | Bandage removal, first wash |
| Days 1-3 | Days 1-3 | Washing routine, ointment, clothing |
| Days 4-14 | Days 4-14 | Peeling, flaking, itching management |
| Weeks 2-4 | Days 15-28 | Deep healing, exercise, sun avoidance |
| Long-Term Care | Day 29+ | Sunscreen, moisturizing, touch-ups |

## Usage

```
[tattoo_aftercare]
```

Drop the shortcode on any page. The plugin handles the rest.

## Requirements

- WordPress 5.8+
- PHP 7.4+

## License

GPL-2.0-or-later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

---

Built by [Primitive Tattoo Bali](https://primitivetattoo.com)
