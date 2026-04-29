(function(){
  const langKey = 'site_lang';
  const defaultLang = 'en';
  const originalTexts = {};

  function saveOriginalTexts(){
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      originalTexts[key] = el.innerHTML;
    });
  }

  async function loadGermanDict(){
    const res = await fetch('assets/i18n/de.json', { cache: 'no-store' });
    return await res.json();
  }

  function applyGerman(dict){
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      if(dict[key]) el.innerHTML = dict[key];
    });
  }

  function applyEnglishOriginal(){
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      if(originalTexts[key] !== undefined) {
        el.innerHTML = originalTexts[key];
      }
    });
  }

  function updateToggle(lang){
    const btn = document.getElementById('langToggle');
    if(!btn) return;
    btn.textContent = (lang === 'en') ? 'DE' : 'EN';
  }

  async function setLang(lang){
    localStorage.setItem(langKey, lang);
    document.documentElement.setAttribute('lang', lang);

    if(lang === 'de'){
      const dict = await loadGermanDict();
      applyGerman(dict);
    } else {
      applyEnglishOriginal();
    }

    updateToggle(lang);
  }

  window.__setLang = setLang;

  document.addEventListener('DOMContentLoaded', async () => {
    saveOriginalTexts();

    const savedLang = localStorage.getItem(langKey) || defaultLang;
    const currentLang = savedLang === 'de' ? 'de' : 'en';

    await setLang(currentLang);

    const btn = document.getElementById('langToggle');
    if(btn){
      btn.addEventListener('click', async () => {
        const current = localStorage.getItem(langKey) || defaultLang;
        await setLang(current === 'en' ? 'de' : 'en');
      });
    }
  });
})();