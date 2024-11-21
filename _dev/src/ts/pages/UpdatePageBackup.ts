import api from '../api/RequestHandler';
import { modalContainer } from '../autoUpgrade';
import ModalContainer from '../components/ModalContainer';
import UpdatePage from './UpdatePage';

export default class UpdatePageBackup extends UpdatePage {
  protected stepCode = 'backup';

  public mount() {
    this.initStepper();
    this.#form.addEventListener('submit', this.#onFormSubmit);
    this.#form.addEventListener('change', this.#onInputChange);

    document.getElementById('ua_container')?.addEventListener('click', this.#onClick);
    modalContainer.modalContainer.addEventListener(ModalContainer.okEvent, this.#onModalOk);
  }

  public beforeDestroy(): void {
    this.#form.removeEventListener('submit', this.#onFormSubmit);
    this.#form.removeEventListener('change', this.#onInputChange);

    document.getElementById('ua_container')?.removeEventListener('click', this.#onClick);
    modalContainer.modalContainer.removeEventListener(ModalContainer.okEvent, this.#onModalOk);
  }

  get #form(): HTMLFormElement {
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

  readonly #onClick = async (ev: Event) => {
    if ((ev.target as HTMLElement).id === 'update-backup-page-skip-btn') {
      const formData = new FormData();
      // TODO: Value currently hardcoded until management of backups is implemented
      formData.append('backupDone', JSON.stringify(false));
      await api.post(this.#form.dataset.routeToSubmitUpdate!, formData);
    }
  };

  readonly #onModalOk = async (ev: Event) => {
    // We handle the backup confirmation modal as it is really basic
    if ((ev.target as HTMLElement).id === 'modal-confirm-backup') {
      api.post(this.#form.dataset.routeToConfirmBackup!);
    }
    // The update confirmation modal gets its logic in a dedicated script
  };

  readonly #onInputChange = async (ev: Event) => {
    const optionInput = ev.target as HTMLInputElement;

    const data = new FormData(this.#form);
    optionInput.setAttribute('disabled', 'true');
    await api.post(this.#form.dataset.routeToSave!, data);
    optionInput.removeAttribute('disabled');
  };

  readonly #onFormSubmit = async (event: Event) => {
    event.preventDefault();

    await api.post(this.#form.dataset.routeToSubmitBackup!, new FormData(this.#form));
  };
}
