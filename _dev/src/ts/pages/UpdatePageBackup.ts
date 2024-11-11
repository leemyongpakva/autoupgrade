import api from '../api/RequestHandler';
import ModalContainer from '../components/ModalContainer';
import UpdatePage from './UpdatePage';

export default class UpdatePageBackup extends UpdatePage {
  protected stepCode = 'backup';
  protected formFields: Element[] = [];

  public mount() {
    this.initStepper();
    this.form.addEventListener('submit', this.onFormSubmit);
    this.form.addEventListener('change', this.onInputChange);

    document.getElementById('ua_container')?.addEventListener('click', this.onClick);
    document
      .getElementById(ModalContainer.containerId)
      ?.addEventListener(ModalContainer.okEvent, (ev) => {
        // We handle the backup confirmation modal as it is really basic
        if ((ev.target as HTMLElement).id === 'modal-confirm-backup') {
          api.post(this.form.dataset.routeToConfirmBackup!);
        }
        // The update confirmation modal gets its logic in a dedicated script
      });
  }

  public beforeDestroy() {
    this.form.removeEventListener('submit', this.onFormSubmit);
    this.form.removeEventListener('change', this.onInputChange);
  }

  private get form(): HTMLFormElement {
    const form = document.forms.namedItem('update-backup-page-form');
    if (!form) {
      throw new Error('Form not found');
    }

    ['routeToSave', 'routeToSubmitBackup', 'routeToSubmitUpdate', 'routeToConfirmBackup'].forEach(
      (data) => {
        if (!form.dataset[data]) {
          throw new Error(`Missing data ${data} from form dataset.`);
        }
      }
    );

    return form;
  }

  private onClick = async (ev: Event) => {
    if ((ev.target as HTMLElement).id === 'update-backup-page-skip-btn') {
      const formData = new FormData();
      formData.append('backupDone', JSON.stringify(false));
      await api.post(this.form.dataset.routeToSubmitUpdate!, formData);
    }
  };

  private onInputChange = async (ev: Event) => {
    const optionInput = ev.target as HTMLInputElement;

    optionInput.setAttribute('disabled', 'true');

    const data = new FormData();
    data.append('name', optionInput.name);
    data.append('value', JSON.stringify(optionInput.checked));
    await api.post(this.form.dataset.routeToSave!, data);

    optionInput.removeAttribute('disabled');
  };

  private onFormSubmit = async (event: Event) => {
    event.preventDefault();

    await api.post(this.form.dataset.routeToSubmitBackup!, new FormData(this.form));
  };
}
