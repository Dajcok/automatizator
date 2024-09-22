export class Translations {
    public static get(lang: string) {
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
    const translations = Translations.get(lang);

    if (!translations) {
        return;
    }

    const saveBtn = document.querySelector('.fr-save-final-button')?.querySelector('button');
    const clearBtn = document.querySelector('.fr-clear-button')?.querySelector('button');

    if (saveBtn) {
        saveBtn.textContent = translations.saveBtn;
    }

    if (clearBtn) {
        clearBtn.textContent = translations.clearBtn;
    }
}
