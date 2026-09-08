const translations = {
  tr: {
    nav: {
      about: "Hakkımda:",
      aboutDesc: "Geliştirici, Linux & Sistem Meraklısı",
      education: "Eğitim:",
      educationDesc: "Okul Geçmişi & Çalışmalar",
      achievements: "Başarılar:",
      achievementsDesc: "Projeler, Modlama & 3D Tasarımlar",
      cv: "CV:",
      cvDesc: "Özgeçmiş & Yetkinlikler",
      contact: "İletişim:",
      contactDesc: "E-posta, GitHub & Sosyal Medya"
    },
    pages: {
      about: "cat about.md",
      education: "cat education.md",
      achievements: "cat achievements.md",
      cv: "cat cv.md",
      contact: "cat contact.md",
      licenses: "cat licenses.md"
    },
    titles: {
      about: "~/hakkimda",
      education: "~/egitim",
      achievements: "~/basarilar",
      cv: "~/cv",
      contact: "~/iletisim",
      licenses: "~/lisanslar"
    },
    licenses: {
      title: "Kullanılan Teknolojiler & Lisanslar",
      license: "Lisans:",
      author: "Yazar:",
      web: "Web:",
      siteTitle: "Site",
      siteDesc: "Bu web sitesi orijinal bir içeriktir. HTML, CSS ve JavaScript ile sıfırdan oluşturulmuştur.",
      mitDesc: "MIT License - Kaynak kodunun yeniden dağıtımının ve türev çalışmaların lisanslanması için bildirimlerin korunmasını gerektirir."
    }
  },
  en: {
    nav: {
      about: "About:",
      aboutDesc: "Developer, Linux & Systems Enthusiast",
      education: "Education:",
      educationDesc: "School History & Studies",
      achievements: "Achievements:",
      achievementsDesc: "Projects, Modding & 3D Designs",
      cv: "CV:",
      cvDesc: "Resume & Skills",
      contact: "Contact:",
      contactDesc: "Email, GitHub & Social Media"
    },
    pages: {
      about: "cat about.md",
      education: "cat education.md",
      achievements: "cat achievements.md",
      cv: "cat cv.md",
      contact: "cat contact.md",
      licenses: "cat licenses.md"
    },
    titles: {
      about: "~/about",
      education: "~/education",
      achievements: "~/achievements",
      cv: "~/cv",
      contact: "~/contact",
      licenses: "~/licenses"
    },
    licenses: {
      title: "Technologies & Licenses",
      license: "License:",
      author: "Author:",
      web: "Web:",
      siteTitle: "Site",
      siteDesc: "This website is original content. Built from scratch with HTML, CSS and JavaScript.",
      mitDesc: "MIT License - Requires preservation of copyright notice and license for redistribution and derivative works."
    }
  }
};

let currentLang = localStorage.getItem('lang') || 'tr';

function setLang(lang) {
  currentLang = lang;
  localStorage.setItem('lang', lang);
  document.documentElement.lang = lang;
}

function getLang() {
  return currentLang;
}

function t(key) {
  const keys = key.split('.');
  let result = translations[currentLang];
  for (const k of keys) {
    result = result?.[k];
  }
  return result || key;
}
