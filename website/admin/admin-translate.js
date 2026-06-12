const ADMIN_LANG_KEY = 'adminLang';
const currentLang = localStorage.getItem(ADMIN_LANG_KEY) || 'badini';

let langDict = {};

async function initAdminLang() {
    try {
        const res = await fetch('admin_lang.json');
        langDict = await res.json();
        
        applyTranslations();
        setupLanguageSwitcher();
    } catch(err) {
        console.error("Failed to load languages", err);
    }
}

function applyTranslations() {
    if (currentLang !== 'english') {
        document.body.dir = 'rtl';
        document.body.style.fontFamily = 'Cairo, sans-serif'; // Usually better for Arabic/Kurdish
    } else {
        document.body.dir = 'ltr';
    }

    if (currentLang === 'english' || !langDict[currentLang]) {
        return;
    }

    const dict = langDict[currentLang];

    // Recursive text replacement
    const walk = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
    let node;
    const textNodes = [];
    while (node = walk.nextNode()) {
        if(node.parentElement && ['SCRIPT', 'STYLE'].includes(node.parentElement.nodeName)) continue;
        textNodes.push(node);
    }
    
    textNodes.forEach(node => {
        const trimmed = node.nodeValue.trim();
        if (trimmed && dict[trimmed]) {
            node.nodeValue = node.nodeValue.replace(trimmed, dict[trimmed]);
        }
    });

    // Translate Inputs/Placeholders
    document.querySelectorAll('input, select, textarea').forEach(el => {
        if (el.placeholder && dict[el.placeholder.trim()]) {
            el.placeholder = dict[el.placeholder.trim()];
        }
    });
}

function switchAdminLang(lang) {
    localStorage.setItem(ADMIN_LANG_KEY, lang);
    location.reload();
}

function setupLanguageSwitcher() {
    const sidebar = document.querySelector('.sidebar-menu');
    if (!sidebar) return;
    
    const langHtml = `
        <div style="padding: 15px; margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1);">
            <p style="font-size: 12px; color: #888; margin-bottom: 8px;">Language</p>
            <select onchange="switchAdminLang(this.value)" style="width: 100%; padding: 8px; background: #222; color: #fff; border: 1px solid #444; border-radius: 5px;">
                <option value="badini" ${currentLang === 'badini' ? 'selected' : ''}>بادیني</option>
                <option value="sorani" ${currentLang === 'sorani' ? 'selected' : ''}>سۆرانی</option>
                <option value="arabic" ${currentLang === 'arabic' ? 'selected' : ''}>العربية</option>
                <option value="english" ${currentLang === 'english' ? 'selected' : ''}>English</option>
            </select>
        </div>
    `;
    
    // Check if sidebar-footer exists, insert before it
    const footer = document.querySelector('.sidebar-footer');
    if (footer) {
        footer.insertAdjacentHTML('beforebegin', langHtml);
    } else {
        sidebar.insertAdjacentHTML('beforeend', langHtml);
    }
}

document.addEventListener("DOMContentLoaded", initAdminLang);
