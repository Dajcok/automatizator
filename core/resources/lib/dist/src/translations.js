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
