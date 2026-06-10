const staticTranslations = {
    badini: {
        backBtn: "زڤرن بۆ لاپەڕێ سەرەكي",
        orderBtn: "جهێ كرنێ ب واتساپێ",
        copyright: "© 2026 <strong>22 Show</strong>. گشت ماف پاراستینە.",
        privacy: "سیاسەتا تایبەتەمەندیێ",
        terms: "مەرجێن بكارهینانێ",
        confirmBtn: "تأكيد الطلب وبەردەوامبە",
        sizeLabel: "قیاس / قەبارە:"
    },
    sorani: {
        backBtn: "گەڕانەوە بۆ لاپەڕەی سەرەکی",
        orderBtn: "داواکردن بە واتساپ",
        copyright: "© 2026 <strong>22 Show</strong>. گشتی مافەکان پارێزراون.",
        privacy: "سیاسەتی تایبەتێتی",
        terms: "مەرجەکانی بەکارهێنان",
        confirmBtn: "پشتڕاستکردنەوەی داواکاری",
        sizeLabel: "قەبارە:"
    },
    arabic: {
        backBtn: "العودة للرئيسية",
        orderBtn: "اطلب عبر واتساب",
        copyright: "© 2026 <strong>22 Show</strong>. جميع الحقوق محفوظة.",
        privacy: "سياسة الخصوصية",
        terms: "شروط الخدمة",
        confirmBtn: "تأكيد الطلب وإرسال",
        sizeLabel: "القياس / الحجم:"
    },
    english: {
        backBtn: "Back to Home",
        orderBtn: "Order via WhatsApp",
        copyright: "© 2026 <strong>22 Show</strong>. All rights reserved.",
        privacy: "Privacy Policy",
        terms: "Terms of Service",
        confirmBtn: "Confirm & Order",
        sizeLabel: "Size / Volume:"
    }
};

let currentOrder = { id: '', title: '', price: '', type: '' };

function switchLanguage(lang) {
    document.body.className = '';
    document.body.classList.add('lang-' + lang);

    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.classList.remove('active');
        if((lang === 'badini' && btn.textContent === 'بادیني') ||
           (lang === 'sorani' && btn.textContent === 'سۆرانی') ||
           (lang === 'arabic' && btn.textContent === 'العربية') ||
           (lang === 'english' && btn.textContent === 'English')) {
            btn.classList.add('active');
        }
    });

    document.querySelector('#backBtn span').textContent = staticTranslations[lang].backBtn;
    document.getElementById('copyrightText').innerHTML = staticTranslations[lang].copyright;
    document.getElementById('privacyLink').textContent = staticTranslations[lang].privacy;
    document.getElementById('termsLink').textContent = staticTranslations[lang].terms;
    document.getElementById('btnModalConfirm').textContent = staticTranslations[lang].confirmBtn;
    
    if(document.getElementById('lblSize')) document.getElementById('lblSize').textContent = staticTranslations[lang].sizeLabel;

    document.querySelectorAll('.product-card').forEach(card => {
        const titleEl = card.querySelector('.prod-title');
        const descEl = card.querySelector('.prod-desc');
        const btnTextEl = card.querySelector('.order-btn .btn-text');

        if(titleEl && titleEl.getAttribute('data-' + lang)) titleEl.textContent = titleEl.getAttribute('data-' + lang);
        if(descEl && descEl.getAttribute('data-' + lang)) descEl.textContent = descEl.getAttribute('data-' + lang);
        if(btnTextEl) btnTextEl.textContent = staticTranslations[lang].orderBtn;
    });

    localStorage.setItem('selectedLanguage', lang);
}

// التحكم بالسلايدر عبر الأسهم والنقرات (PC)
function changeSlide(productId, direction, event) {
    if (event) event.stopPropagation();
    
    const card = document.getElementById(productId);
    if (!card) return;

    const slider = card.querySelector('.images-slider');
    if (!slider) return;

    const slideWidth = slider.clientWidth;
    const scrollAmount = slideWidth * direction;
    
    slider.scrollBy({
        left: scrollAmount,
        behavior: 'smooth'
    });
    
    // Track Swipe Action
    fetch('track.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: productId, action: 'swipe' })
    }).catch(e => console.error('Tracking error:', e));
}

// تحديث نقاط التتبع (Dots) فوراً وتلقائياً عند السحب باليد (Touch Swipe)
function initSliderScrollListeners() {
    document.querySelectorAll('.product-card').forEach(card => {
        const slider = card.querySelector('.images-slider');
        const dots = card.querySelectorAll('.slider-dots .dot');
        
        if (!slider || dots.length === 0) return;

        slider.addEventListener('scroll', () => {
            const slideWidth = slider.clientWidth;
            const currentIndex = Math.round(slider.scrollLeft / slideWidth);
            
            dots.forEach((dot, idx) => {
                if (idx === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
            
            // Track swipe when using touch/scrolling
            const lastIndex = slider.getAttribute('data-last-index') || 0;
            if (currentIndex != lastIndex) {
                slider.setAttribute('data-last-index', currentIndex);
                fetch('track.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: card.id, action: 'swipe' })
                }).catch(e => console.error('Tracking error:', e));
            }
        });
    });
}

function openOrderModal(productId) {
    const card = document.getElementById(productId);
    if (!card) return;

    currentOrder.id = productId;
    currentOrder.title = card.querySelector('.prod-title').textContent;
    currentOrder.price = card.querySelector('.price').textContent;
    currentOrder.type = card.getAttribute('data-type') || 'general';

    document.getElementById('modalProductTitle').textContent = currentOrder.title;
    document.getElementById('modalProductPrice').textContent = currentOrder.price;
    document.getElementById('prodQty').value = 1;

    const sizeSelect = document.getElementById('prodSize');
    const sizeGroup = document.getElementById('sizeGroup');
    sizeSelect.innerHTML = '';

    if (currentOrder.type === 'shoes') {
        sizeGroup.style.display = 'flex';
        const shoeSizes = ['39', '40', '41', '42', '43', '44', '45'];
        shoeSizes.forEach(sz => { sizeSelect.innerHTML += `<option value="${sz}">${sz}</option>`; });
    } else if (currentOrder.type === 'perfume') {
        sizeGroup.style.display = 'flex';
        const perfumeSizes = ['50ml', '100ml'];
        perfumeSizes.forEach(sz => { sizeSelect.innerHTML += `<option value="${sz}">${sz}</option>`; });
    } else {
        sizeGroup.style.display = 'none';
    }

    document.getElementById('orderModal').classList.add('show');
}

function closeModal() {
    document.getElementById('orderModal').classList.remove('show');
}

function updateQty(amount) {
    const qtyInput = document.getElementById('prodQty');
    let currentQty = parseInt(qtyInput.value) || 1;
    currentQty += amount;
    if (currentQty < 1) currentQty = 1;
    qtyInput.value = currentQty;
}

function submitToWhatsApp() {
    const currentLang = localStorage.getItem('selectedLanguage') || 'badini';
    const phoneNumber = (typeof storeSettings !== 'undefined' && storeSettings.whatsapp_number) ? storeSettings.whatsapp_number : "9647501859616";
    
    const qty = document.getElementById('prodQty').value;
    const sizeSelect = document.getElementById('prodSize');
    const hasSize = (document.getElementById('sizeGroup').style.display !== 'none');
    const selectedSize = hasSize ? sizeSelect.value : '';

    const storeName = (typeof storeSettings !== 'undefined' && storeSettings.store_name) ? storeSettings.store_name : "22 Show";
    let msgHello = "سلاڤ " + storeName + "، حەز دكەم ڤي تشتي داوا بكەم:\n\n";
    let labelItem = "ناڤێ تشتي: "; let labelPrice = "بهایێ یەکەیێ: "; let labelQty = "چەند دانە: "; let labelSize = "قیاس: ";

    if(currentLang === 'sorani') {
        msgHello = "سلاو " + storeName + "، حەزم لێیە ئەم داواکارییە بکەم:\n\n";
        labelItem = "ناوى کاڵا: "; labelPrice = "نرخی تاک: "; labelQty = "ژمارە: "; labelSize = "قەبارە: ";
    } else if(currentLang === 'arabic') {
        msgHello = "مرحبا " + storeName + "، أود طلب هذا المنتج بالخيارات التالية:\n\n";
        labelItem = "اسم المنتج: "; labelPrice = "سعر القطعة: "; labelQty = "الكمية المطلوبة: "; labelSize = "القياس المحدد: ";
    } else if(currentLang === 'english') {
        msgHello = "Hello " + storeName + ", I would like to order this item:\n\n";
        labelItem = "Product Name: "; labelPrice = "Price per Unit: "; labelQty = "Quantity: "; labelSize = "Selected Size: ";
    }

    let message = msgHello;
    message += "📦 " + labelItem + currentOrder.title + "\n";
    message += "💰 " + labelPrice + currentOrder.price + "\n";
    message += "🔢 " + labelQty + qty + "x\n";
    if(hasSize) message += "📏 " + labelSize + selectedSize + "\n";
    
    // Track WhatsApp Click
    fetch('track.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: currentOrder.id, action: 'whatsapp' })
    }).catch(e => console.error('Tracking error:', e));
    
    const whatsappLink = "https://wa.me/" + phoneNumber + "?text=" + encodeURIComponent(message);
    window.open(whatsappLink, '_blank');
    closeModal();
}

function initViewTracking() {
    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const prodId = entry.target.id;
                // Track View
                fetch('track.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: prodId, action: 'view' })
                }).catch(e => console.error('Tracking error:', e));
                
                // Stop observing once viewed
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 }); // Trigger when 50% of the product card is visible

    document.querySelectorAll('.product-card').forEach(card => {
        observer.observe(card);
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const savedLang = localStorage.getItem('selectedLanguage') || 'badini';
    switchLanguage(savedLang);
    initSliderScrollListeners();
    initViewTracking();
    
    window.onclick = function(event) {
        const modal = document.getElementById('orderModal');
        if (event.target === modal) closeModal();
    }
});
