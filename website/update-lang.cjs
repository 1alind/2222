const fs = require('fs');
let lang = JSON.parse(fs.readFileSync('website/admin/admin_lang.json', 'utf8')); 

const words = { 
    'General': { badini: 'گشتی', sorani: 'گشتی', arabic: 'عام' }, 
    'Shoes': { badini: 'پێلاڤ', sorani: 'پێڵاو', arabic: 'أحذية' }, 
    'Perfume': { badini: 'بێهنخۆش', sorani: 'بۆن', arabic: 'عطور' }, 
    'Watch': { badini: 'کاتژمێر', sorani: 'کاتژمێر', arabic: 'ساعات' }, 
    'Clothing': { badini: 'جلوبەرگ', sorani: 'جلوبەرگ', arabic: 'ملابس' }, 
    'T-Shirt': { badini: 'تی شێرت', sorani: 'تیشێرت', arabic: 'تي شيرت' },
    'Shirts': { badini: 'کراس', sorani: 'کراس', arabic: 'قمصان' },
    'Trousers': { badini: 'پانتۆل', sorani: 'پانتۆڵ', arabic: 'بناطيل' },
    'Jeans': { badini: 'کابۆ', sorani: 'پانتۆڵی کابۆ', arabic: 'جينز' },
    'Shorts': { badini: 'شورت', sorani: 'شۆرت', arabic: 'شورت' },
    'Accessories': { badini: 'ئێکسسوارات', sorani: 'ئێکسسوارات', arabic: 'إكسسوارات' },
    'Glasses': { badini: 'بەرچاوک', sorani: 'چاویلکە', arabic: 'نظارات' },
    'Hats': { badini: 'کڵاو', sorani: 'کڵاو', arabic: 'قبعات' },
    'Available Sizes': { badini: 'قیاسێن بەردەست', sorani: 'قەبارە بەردەستەکان', arabic: 'المقاسات المتاحة' } 
};

for(let w in words) {
    if(lang.badini) lang.badini[w] = words[w].badini;
    if(lang.sorani) lang.sorani[w] = words[w].sorani;
    if(lang.arabic) lang.arabic[w] = words[w].arabic;
}

fs.writeFileSync('website/admin/admin_lang.json', JSON.stringify(lang, null, 4));
console.log("Updated lang");
