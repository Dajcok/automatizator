var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { BaseRenderer } from "./base.renderer";
export class FormRenderer extends BaseRenderer {
    render(container, pageUrl) {
        return super.render(container, pageUrl, (container) => {
            //Attach classes to the container that are required by Orbeon
            container.classList.add('orbeon');
            container.classList.add('xforms-disable-alert-as-tooltip');
            container.classList.add('yui-skin-sam');
        });
    }
    submitControl(context) {
        return __awaiter(this, void 0, void 0, function* () {
            const els = document.querySelectorAll(`select[id*="${context.control}"]`);
            if (!els.length) {
                console.warn(`No select found for control ${context.control}. Is the form mounted?`);
            }
            ;
            const el = els[0];
            const optionToSelect = Array.from(el.options).find((option) => option.title === String(context.data));
            if (optionToSelect) {
                optionToSelect.selected = true;
            }
        });
    }
}
