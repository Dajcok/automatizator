export class Translations {
    static get(lang) {
        switch (lang) {
            case 'sk':
                return {
                    saveBtn: 'Uložiť',
                    clearBtn: 'Vymazať',
                    successMsg: 'Dokument bol úspešne uložený!',
                };
        }
    }
}
export function translate(lang = "sk") {
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
