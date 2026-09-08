# fetchfolio

Kişisel portfolyo web sitesi. Terminal temalı, TR/EN dil destekli.

## Özellikler

- Terminal simülasyonu arayüzü
- ASCII art logo (custom.yaml'dan değiştirilebilir)
- TR/EN dil desteği
- Responsive tasarım
- custom.yaml ile kolay özelleştirme

## Kurulum

```bash
git clone https://github.com/farukylmz0550/fetchfolio.git
cd fetchfolio
python3 -m http.server 8000
```

Tarayıcıda açın: `http://localhost:8000`

## Özelleştirme

`custom.yaml` dosyasını düzenleyerek siteyi özelleştirebilirsiniz:

```yaml
site:
  title: "farukylmz@thinkpad:~/"
  user: "farukylmz"
  host: "thinkpad"

ascii_art: |
  =-%%%%%%%%%%%%%%%%%%%%%%%%%==
  ... (ASCII art buraya)

theme:
  accent_color: "#38bdf8"
```

## Lisans

Bu proje GNU General Public License v3.0 ile lisanslanmıştır.
Ayrıntılar için [LICENSE](LICENSE) dosyasına bakın.

## Kullanılan Teknolojiler

- HTML5
- CSS3
- JavaScript
- marked.js (Markdown parser)
- js-yaml (YAML parser)
