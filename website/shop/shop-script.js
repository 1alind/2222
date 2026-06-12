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
        const priceEl = card.querySelector('.price');

        if(titleEl && titleEl.getAttribute('data-' + lang)) titleEl.textContent = titleEl.getAttribute('data-' + lang);
        if(descEl && descEl.getAttribute('data-' + lang)) descEl.innerHTML = descEl.getAttribute('data-' + lang);
        if(btnTextEl) btnTextEl.textContent = staticTranslations[lang].orderBtn;
        
        if(priceEl) {
            let rawPrice = priceEl.getAttribute('data-raw-price') || '';
            // Strip any existing currency texts from old products, preserve numbers and commas
            rawPrice = rawPrice.replace(/[^\d.,]/g, '').trim(); 
            
            // Re-format just in case there are missing commas and strip trailing dots
            rawPrice = rawPrice.replace(/\.+$/, '');
            let numericVal = rawPrice.replace(/,/g, '');
            if(!isNaN(numericVal) && numericVal !== '') {
                rawPrice = Number(numericVal).toLocaleString('en-US');
            }
            
            if (rawPrice !== '') {
                if (lang === 'english') {
                    priceEl.textContent = rawPrice + ' Iraqi Dinar';
                } else {
                    priceEl.textContent = rawPrice + ' دينار عراقي';
                }
            }
        }
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

    let customSizesData = card.getAttribute('data-sizes');
    let customMlData = card.getAttribute('data-ml');
    let customSizes = customSizesData ? JSON.parse(customSizesData) : null;
    let customMl = customMlData ? JSON.parse(customMlData) : null;

    if (customSizes && customSizes.length > 0) {
        sizeGroup.style.display = 'flex';
        customSizes.forEach(sz => { sizeSelect.innerHTML += `<option value="${sz}">${sz}</option>`; });
    } else if (customMl && customMl.length > 0) {
        sizeGroup.style.display = 'flex';
        customMl.forEach(sz => { sizeSelect.innerHTML += `<option value="${sz}">${sz}</option>`; });
    } else if (currentOrder.type === 'shoes') {
        sizeGroup.style.display = 'flex';
        const shoeSizes = ['36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46'];
        shoeSizes.forEach(sz => { sizeSelect.innerHTML += `<option value="${sz}">${sz}</option>`; });
    } else if (currentOrder.type === 'perfume') {
        sizeGroup.style.display = 'flex';
        const perfumeSizes = ['30ml', '50ml', '75ml', '100ml'];
        perfumeSizes.forEach(sz => { sizeSelect.innerHTML += `<option value="${sz}">${sz}</option>`; });
    } else if (['clothing', 'tshirt', 'shirts', 'shorts'].includes(currentOrder.type)) {
        sizeGroup.style.display = 'flex';
        const clothSizes = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '5XL', '6XL'];
        clothSizes.forEach(sz => { sizeSelect.innerHTML += `<option value="${sz}">${sz}</option>`; });
    } else if (['jeans', 'trousers'].includes(currentOrder.type)) {
        sizeGroup.style.display = 'flex';
        const jeanSizes = ['28', '29', '30', '31', '32', '33', '34', '36', '38', '40', '42'];
        jeanSizes.forEach(sz => { sizeSelect.innerHTML += `<option value="${sz}">${sz}</option>`; });
    } else {
        sizeGroup.style.display = 'none';
    }

    document.getElementById('orderModal').classList.add('show');
}

function closeModal() {
    document.getElementById('orderModal').classList.remove('show');
}

function openImageModal(imgSrc) {
    document.getElementById('enlargedImg').src = imgSrc;
    document.getElementById('imageModal').classList.add('show');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.remove('show');
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

let currentCategory = 'all';

function filterProducts() {
    const searchTerm = document.getElementById('shopSearch').value.toLowerCase();
    const cards = document.querySelectorAll('.product-card');
    
    cards.forEach(card => {
        const titleEl = card.querySelector('.prod-title');
        const descEl = card.querySelector('.prod-desc');
        
        let matchSearch = false;
        if (titleEl) {
            const tBadini = (titleEl.getAttribute('data-badini') || '').toLowerCase();
            const tSorani = (titleEl.getAttribute('data-sorani') || '').toLowerCase();
            const tArabic = (titleEl.getAttribute('data-arabic') || '').toLowerCase();
            const tEnglish = (titleEl.getAttribute('data-english') || '').toLowerCase();
            
            if (tBadini.includes(searchTerm) || tSorani.includes(searchTerm) || tArabic.includes(searchTerm) || tEnglish.includes(searchTerm)) {
                matchSearch = true;
            }
        }
        
        if (!matchSearch && descEl) {
            const dBadini = (descEl.getAttribute('data-badini') || '').toLowerCase();
            const dSorani = (descEl.getAttribute('data-sorani') || '').toLowerCase();
            const dArabic = (descEl.getAttribute('data-arabic') || '').toLowerCase();
            const dEnglish = (descEl.getAttribute('data-english') || '').toLowerCase();
            
            if (dBadini.includes(searchTerm) || dSorani.includes(searchTerm) || dArabic.includes(searchTerm) || dEnglish.includes(searchTerm)) {
                matchSearch = true;
            }
        }
        
        let matchCat = false;
        if (currentCategory === 'all') {
            matchCat = true;
        } else {
            const type = card.getAttribute('data-type');
            if (type === currentCategory) {
                matchCat = true;
            }
        }
        
        if (matchSearch && matchCat) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });

    // إخفاء/إظهار الأقسام التي ليس لديها منتجات
    document.querySelectorAll('.product-category-section').forEach(section => {
        let hasVisible = false;
        section.querySelectorAll('.product-card').forEach(c => {
            if (c.style.display !== 'none') hasVisible = true;
        });
        
        if (hasVisible) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
}

function filterCategory(category) {
    currentCategory = category;
    
    document.querySelectorAll('.cat-btn').forEach(btn => {
        if (btn.getAttribute('data-cat') === category) {
            btn.classList.add('active');
            btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        } else {
            btn.classList.remove('active');
        }
    });
    
    filterProducts();
}

document.addEventListener("DOMContentLoaded", () => {
    const savedLang = localStorage.getItem('selectedLanguage') || 'badini';
    switchLanguage(savedLang);
    initSliderScrollListeners();
    initViewTracking();
    
    window.onclick = function(event) {
        const orderModal = document.getElementById('orderModal');
        const imgModal = document.getElementById('imageModal');
        if (event.target === orderModal) closeModal();
        if (event.target === imgModal) closeImageModal();
    }
});
