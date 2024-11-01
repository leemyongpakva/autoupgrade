import api from '../api/RequestHandler';
import ModalContainer from '../components/ModalContainer';
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
    document.getElementById('ua_container')?.addEventListener('click', this.onClick);
    document.getElementById(ModalContainer.containerId)?.addEventListener(ModalContainer.okEvent, (ev) => {
      if (ev.target.id === 'modal-confirm-backup') {
        api.post(this.form.dataset.routeToConfirmBackup!);
      } else if (ev.target.id === 'modal-confirm-update') {
        api.post(this.form.dataset.routeToConfirmUpdate!);
      }
    });
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

    ['routeToSave', 'routeToSubmitBackup', 'routeToSubmitUpdate', 'routeToConfirmBackup', 'routeToConfirmUpdate'].forEach((data) => {
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

  private onClick = (ev: Event) => {
    if (ev.target.id === 'update-backup-page-skip-btn') {
      const formData = new FormData();
      formData.append('backupDone', JSON.stringify(false));
      api.post(this.form.dataset.routeToSubmitUpdate!, formData);
    }
  };

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

    api.post(this.form.dataset.routeToSubmitBackup!, new FormData(this.form));
  };
}
