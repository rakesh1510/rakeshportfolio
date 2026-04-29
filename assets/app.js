
(function(){
  const toggle = document.querySelector('[data-nav-toggle]');
  const closeOnClickLinks = document.querySelectorAll('.mobileMenu a');
  if(toggle){
    toggle.addEventListener('click', ()=>{
      document.body.classList.toggle('navOpen');
      const expanded = document.body.classList.contains('navOpen');
      toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    });
  }
  closeOnClickLinks.forEach(a=>{
    a.addEventListener('click', ()=>document.body.classList.remove('navOpen'));
  });

  // Close when clicking outside
  document.addEventListener('click', (e)=>{
    const nav = document.querySelector('.navWrap');
    if(!nav) return;
    if(document.body.classList.contains('navOpen') && !nav.contains(e.target)){
      document.body.classList.remove('navOpen');
      if(toggle) toggle.setAttribute('aria-expanded','false');
    }
  });
})();


// Scroll reveal
(function(){
  const els = Array.from(document.querySelectorAll('.reveal'));
  if(!els.length) return;
  const obs = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{
      if(e.isIntersecting){
        e.target.classList.add('is-visible');
        obs.unobserve(e.target);
      }
    });
  }, {threshold:0.12, rootMargin:'0px 0px -10% 0px'});
  els.forEach(el=>obs.observe(el));
})();
