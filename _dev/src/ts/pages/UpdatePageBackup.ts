import api from '../api/RequestHandler';
import UpdatePage from './UpdatePage';

export default class UpdatePageBackup extends UpdatePage {
  protected stepCode = 'backup';
  protected form: HTMLFormElement;
  protected formFields: Element[] = [];

  constructor() {
    super();

    this.form = this.assertAndGetForm();
  }

  public mount() {
    this.initStepper();
    this.initFormFields(this.form);
    this.form.addEventListener('submit', this.onFormSubmit);
  }

  public beforeDestroy() {
    this.form.removeEventListener('submit', this.onFormSubmit);
    this.formFields.forEach((element) => {
      element.removeEventListener('change', this.onInputChange);
    });
  }

  private assertAndGetForm(): HTMLFormElement {
    const form = document.forms.namedItem('update-backup-page-form');
    if (!form) {
      throw new Error('Form not found');
    }

    ['routeToSave', 'routeToSubmit'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }

  private initFormFields(form: HTMLFormElement): void {
    Array.from(form.elements).forEach((element) => {
      if (element.nodeName !== 'INPUT') {
        return;
      }

      this.formFields.push(element);

      element.addEventListener('change', this.onInputChange);
    });
  }

  private onInputChange = (ev: Event) => {
    const optionInput = ev.target as HTMLInputElement;

    optionInput.setAttribute('disabled', 'true');

    const data = new FormData();
    data.append('name', optionInput.name);
    data.append('value', JSON.stringify(optionInput.checked));
    api.post(this.form.dataset.routeToSave!, data);

    optionInput.removeAttribute('disabled');
  };

  private onFormSubmit = (event: Event) => {
    event.preventDefault();

    api.post(this.form.dataset.routeToSubmit!, new FormData(this.form));
  };
}
