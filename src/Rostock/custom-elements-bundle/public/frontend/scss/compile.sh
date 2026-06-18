#!/usr/bin/env bash
set -euo pipefail

DIR="$(cd "$(dirname "$0")" && pwd)"

sass "$DIR/psa_fonts.scss" "$DIR/../css/psa_fonts.css" --style=expanded --no-source-map
sass "$DIR/psa_button.scss" "$DIR/../css/psa_button.css" --style=expanded --no-source-map
sass "$DIR/psa_hero.scss" "$DIR/../css/psa_hero.css" --style=expanded --no-source-map
sass "$DIR/psa_site_header.scss" "$DIR/../css/psa_site_header.css" --style=expanded --no-source-map
sass "$DIR/psa_site_footer.scss" "$DIR/../css/psa_site_footer.css" --style=expanded --no-source-map
sass "$DIR/psa_member_forms.scss" "$DIR/../css/psa_member_forms.css" --style=expanded --no-source-map
sass "$DIR/ce_text_double.scss" "$DIR/../css/ce_text_double.css" --style=expanded --no-source-map
sass "$DIR/ce_slider_main.scss" "$DIR/../css/ce_slider_main.css" --style=expanded --no-source-map

ROOT="$(cd "$DIR/../../../../../.." && pwd)"
sass "$ROOT/files/tpl/scss/main.scss" "$ROOT/files/tpl/css/main.css" --style=expanded --no-source-map --load-path="$ROOT/node_modules"

echo "Compiled psa_fonts.css, psa_button.css, psa_hero.css, psa_site_header.css, psa_site_footer.css, psa_member_forms.css, ce_text_double.css, ce_slider_main.css, main.css"
