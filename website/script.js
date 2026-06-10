  // -------------------------
  // SHINE EFFECT
  // -------------------------
  const buttons = document.querySelectorAll(".link-card");
  let shineIndex = 0;

  function runShine() {
    if (buttons.length === 0) return;

    const el = buttons[shineIndex];

    el.classList.remove("shine");
    void el.offsetWidth;
    el.classList.add("shine");

    shineIndex = (shineIndex + 1) % buttons.length;

    setTimeout(runShine, 5000);
  }

  runShine();

  // -------------------------
  // EMOJI ROTATOR
  // -------------------------
  const EMOJIS = ['👕','👖','👟','🥾','🩳','🧦','🧢','🕶️','⌚️','🚕'];
const emojiEl = document.getElementById("emoji-slider");

let index = 0;

function changeEmoji() {

    emojiEl.classList.add("flip-out");

    setTimeout(() => {

        index = (index + 1) % EMOJIS.length;
        emojiEl.textContent = EMOJIS[index];

        emojiEl.classList.remove("flip-out");
        emojiEl.classList.add("flip-in");

        setTimeout(() => {
            emojiEl.classList.remove("flip-in");
        }, 400);

    }, 400);
}

setInterval(changeEmoji, 2000);

const actualPhone = typeof storeSettings !== 'undefined' ? storeSettings.whatsapp_number : "9647501859616";

// Pure JSON data mapping for your URLs
const urlDatabase = {
    "whatsapp": "https://wa.me/" + actualPhone + "?text=" + encodeURIComponent("I'm interested in"),
    "instagram": typeof storeSettings !== 'undefined' ? storeSettings.instagram : "https://instagram.com/22show_",
    "tiktok": typeof storeSettings !== 'undefined' ? storeSettings.tiktok : "https://www.tiktok.com/@22show_",
    "snapchat": typeof storeSettings !== 'undefined' ? storeSettings.snapchat : "https://www.snapchat.com/@twenty2_show",
    "applemaps": typeof storeSettings !== 'undefined' ? storeSettings.apple_maps : "https://maps.apple.com/...",
    "googlemaps": typeof storeSettings !== 'undefined' ? storeSettings.google_maps : "https://maps.app.goo.gl/G7xczmrF6BWAdxoCA",
    "shop": "./shop"
};

// Function updated to force a new tab target
function openUrl(platform) {
    const destinationUrl = urlDatabase[platform];
    if (destinationUrl) {
        // Track the click to our backend API asynchronously
        fetch('./shop/track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: platform, action: 'click' })
        }).catch(e => console.error('Tracking error:', e));

        window.open(destinationUrl, '_blank');
    }
}





/* ######################
Save Contact
###################### */

// Convert image URL → Base64
async function getBase64FromUrl(url) {
    const response = await fetch(url);

    if (!response.ok) {
        throw new Error("Failed to load image");
    }

    const blob = await response.blob();

    return new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onloadend = () => {
            const base64 = reader.result.split(",")[1];
            resolve(base64);
        };

        reader.onerror = reject;
        reader.readAsDataURL(blob);
    });
}

// Optional: prevent vCard line breaking issues
function foldLine(line, maxLength = 75) {
    let result = "";

    while (line.length > maxLength) {
        result += line.substring(0, maxLength) + "\r\n ";
        line = line.substring(maxLength);
    }

    return result + line;
}

// MAIN FUNCTION
async function saveContact() {
    if (typeof urlDatabase === "undefined") {
        console.error("Error: urlDatabase is not defined.");
        return;
    }

    try {
        // 1. Get image from your URL and convert to base64
        const cleanBase64 = await getBase64FromUrl(
            "https://gbtwjets.rf.gd/22/22show_logo.jpeg"
        );

        // 2. Contact data
        const contact = {
            name: typeof storeSettings !== 'undefined' ? storeSettings.store_name : "22 Show",
            phone: "+" + actualPhone,
            latitude: typeof storeSettings !== 'undefined' ? storeSettings.latitude : "36.850062",
            longitude: typeof storeSettings !== 'undefined' ? storeSettings.longitude : "43.038856",
            googleMapsUrl: urlDatabase.googlemaps,
            appleMapsUrl: urlDatabase.applemaps,
            instagram: urlDatabase.instagram,
            tiktok: urlDatabase.tiktok,
            snapchat: urlDatabase.snapchat,
            whatsapp: urlDatabase.whatsapp
        };

        // 3. PHOTO field (most important part)
        const photoLine = foldLine(
            `PHOTO;TYPE=JPEG;ENCODING=b:${cleanBase64}`
        );

        // 4. Build vCard
        const vcard = [
            "BEGIN:VCARD",
            "VERSION:3.0",
            `FN:${contact.name}`,
            `ORG:${contact.name}`,
            `TEL;TYPE=CELL,VOICE:${contact.phone}`,
            `GEO:${contact.latitude};${contact.longitude}`,
            `URL;TYPE=GoogleMaps:${contact.googleMapsUrl}`,
            `URL;TYPE=AppleMaps:${contact.appleMapsUrl}`,
            `X-SOCIALPROFILE;TYPE=instagram:${contact.instagram}`,
            `X-SOCIALPROFILE;TYPE=tiktok:${contact.tiktok}`,
            `X-SOCIALPROFILE;TYPE=snapchat:${contact.snapchat}`,
            `URL;TYPE=WhatsApp:${contact.whatsapp}`,
            photoLine,
            "END:VCARD"
        ].join("\r\n");

        // 5. Download file
        const blob = new Blob([vcard], {
            type: "text/vcard;charset=utf-8;"
        });

        const url = window.URL.createObjectURL(blob);

        const downloadLink = document.createElement("a");
        downloadLink.href = url;
        downloadLink.download = `${contact.name}.vcf`;

        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);

        window.URL.revokeObjectURL(url);

    } catch (error) {
        console.error("Failed to create contact:", error);
        alert("Could not load image for contact.");
    }
}