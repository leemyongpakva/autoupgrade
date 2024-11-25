import UpdatePage from './UpdatePage';
import api from '../api/RequestHandler';

export default class UpdatePageUpdateOptions extends UpdatePage {
  protected stepCode = 'update-options';

  public mount() {
    this.initStepper();
    this.#form.addEventListener('submit', this.#onSubmit);
    this.#form.addEventListener('change', this.#onChange);
  }

  public beforeDestroy() {
    try {
      this.#form.removeEventListener('submit', this.#onSubmit);
      this.#form.removeEventListener('change', this.#onChange);
    } catch {
      // Do Nothing, page is likely removed from the DOM already
    }
  }

  get #form(): HTMLFormElement {
    const form = document.forms.namedItem('update-options-page-form');
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

  readonly #onChange = async (ev: Event) => {
    const optionInput = ev.target as HTMLInputElement;

    const data = new FormData(this.#form);
    optionInput.setAttribute('disabled', 'true');
    await api.post(this.#form.dataset.routeToSave!, data);
    optionInput.removeAttribute('disabled');
  };

  readonly #onSubmit = async (event: Event) => {
    event.preventDefault();

    await api.post(this.#form.dataset.routeToSubmit!, new FormData(this.#form));
  };
}
