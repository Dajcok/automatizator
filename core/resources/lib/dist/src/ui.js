import { Translations } from "./translations";
/**
 * This function is used to customize the UI of the Orbeon Forms. It should be called once
 */
export function customizeUI() {
    // removeUnnecessaryElements();
    translate();
}
/**
 * This can be done using CSS, but it's better to remove the elements from the DOM
 */
// function removeUnnecessaryElements() {
//     const buttonsToRemove = document.querySelectorAll('.fr-pdf-button, .fr-review-button, .fr-summary-button');
//
//     if (buttonsToRemove.length > 0) {
//         buttonsToRemove.forEach(button => {
//             button.remove();
//         });
//     }
// }
function translate(lang = "sk") {
    var _a, _b;
    const translations = Translations.get(lang);
    if (!translations) {
        return;
    }
    const saveBtn = (_a = document.querySelector('.fr-save-final-button')) === null || _a === void 0 ? void 0 : _a.querySelector('button');
    const clearBtn = (_b = document.querySelector('.fr-clear-button')) === null || _b === void 0 ? void 0 : _b.querySelector('button');
    if (saveBtn) {
        saveBtn.textContent = translations.saveBtn;
    }
    if (clearBtn) {
        clearBtn.textContent = translations.clearBtn;
    }
}
