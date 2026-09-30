# fetchfolio

Kişisel portfolyo web sitesi. Terminal temalı, TR/EN dil destekli.

<img width="1920" height="962" alt="Screenshot From 2026-09-09 12-32-44" src="https://github.com/user-attachments/assets/b7811ce4-c817-4bc5-add6-80221a14b9af" />

## Özellikler

- Terminal simülasyonu arayüzü (neofetch tarzı)
- ASCII art logo (`custom.yaml`'dan değiştirilebilir)
- TR/EN dil desteği
- Responsive tasarım
- `custom.yaml` ile kolay özelleştirme (başlık, kullanıcı@host, vurgu rengi, sosyal linkler)
- Docker + GitHub Pages deploy desteği

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
  version: "1.0.0"
  title: "user@host:~/"
  user: "fetch"
  host: "folio"

theme:
  accent_color: "#38bdf8"

# Sosyal linkler — URL boş bırakılan ağların kutusu gizlenir
social:
  github: "https://github.com/farukylmz0550"
  youtube: ""
  makerworld: ""
  huggingface: ""
  gravatar: ""
  discord: ""
```

İçerik sayfaları `content/tr/` ve `content/en/` altındaki Markdown dosyalarından
okunur (`about.md`, `education.md`, `achievements.md`, `cv.md`, `contact.md`,
`licenses.md`).

## Docker ile Çalıştırma

```bash
docker compose up --build
# → http://localhost:8080
```

Önceden derlenmiş imaj (ghcr.io'dan):

```bash
docker run --rm -p 8080:80 ghcr.io/farukylmz0550/fetchfolio:latest
```

`main` branch'ine her push'ta imaj otomatik derlenir ve `ghcr.io`'ya yayınlanır.
`v*` formatında git tag'ları (ör. `v1.0.0`) sürüm etiketli imaj üretir.

## GitHub Pages

`main`'e her push otomatik olarak GitHub Pages'e deploy edilir:
**https://farukylmz0550.github.io/fetchfolio/**

- Terminal temalı `404.html` sayfası GH Pages'ta eksik yollarda otomatik görünür.
- Site alt dizinde (`/fetchfolio/`) servis edildiği için tüm yollar relative
  tanımlıdır; ek bir konfigürasyon gerekmez.

## Sürümlendirme

[Semver](https://semver.org/) kullanılır: kırıcı değişiklikler major,
özellikler minor, düzeltmeler patch.

- `1.0.0` — ilk resmi sürüm: CSS tekilleştirme, sosyal link yapılandırması,
  erişilebilirlik iyileştirmeleri, Docker + GH Pages desteği

## Lisans

Bu proje GNU General Public License v3.0 ile lisanslanmıştır.
Ayrıntılar için [LICENSE](LICENSE) dosyasına bakın.

## Kullanılan Teknolojiler

- HTML5
- CSS3
- JavaScript
- marked.js (Markdown parser)
- js-yaml (YAML parser)
