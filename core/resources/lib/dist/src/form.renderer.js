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
}
