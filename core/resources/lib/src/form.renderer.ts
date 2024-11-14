import {BaseRenderer} from "./base.renderer";

export class FormRenderer extends BaseRenderer {
    public render(
        container: HTMLElement,
        pageUrl: string,
    ) {
        return super.render(container, pageUrl, (container) => {
            //Attach classes to the container that are required by Orbeon
            container.classList.add('orbeon');
            container.classList.add('xforms-disable-alert-as-tooltip');
            container.classList.add('yui-skin-sam');
        });
    }

    public async submitControl(
        context: {
            control: string;
            data: number | string;
        },
    ) {
        const els = document.querySelectorAll(`select[id*="${context.control}"]`);

        if (!els.length) {
            console.warn(`No select found for control ${context.control}. Is the form mounted?`);
        };

        const el = els[0] as HTMLSelectElement;

        const optionToSelect = Array.from(el.options).find(
            (option) => option.title === String(context.data)
        ) as HTMLOptionElement;

        if (optionToSelect) {
            optionToSelect.selected = true;
        }
    }
}
