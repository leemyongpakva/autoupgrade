import ComponentAbstract from '../types/ComponentAbstract';
import api from '../api/RequestHandler';

export default class StartUpdateModal extends ComponentAbstract {
  protected readonly formId = 'form-confirm-update';
  protected readonly confirmCheckboxId = 'modal-start-update-own-backup';

  public mount() {
    this.form.addEventListener('submit', this.onSubmit);
    this.form.addEventListener('change', this.onChange);

    this.updateSubmitButtonStatus(
      document.getElementById('modal-start-update-own-backup') as HTMLInputElement | undefined
    );
  }

  public beforeDestroy() {
    this.form.removeEventListener('submit', this.onSubmit);
    this.form.removeEventListener('change', this.onChange);
  }

  private get form(): HTMLFormElement {
    const form = document.forms.namedItem('form-confirm-update');
    if (!form) {
      throw new Error('Form not found');
    }

    ['routeToSubmit'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }

  private get submitButton(): HTMLButtonElement {
    const submitButton = Array.from(this.form.elements).find(
      (element) => element instanceof HTMLButtonElement && element.type === 'submit'
    ) as HTMLButtonElement | null;

    if (!submitButton) {
      throw new Error(`No submit button found for form ${this.form.id}`);
    }

    return submitButton;
  }

  private readonly onChange = async (ev: Event) => {
    const optionInput = ev.target as HTMLInputElement;

    if (optionInput.id === this.confirmCheckboxId) {
      this.updateSubmitButtonStatus(optionInput);
    }
  };

  private readonly onSubmit = async (event: Event) => {
    event.preventDefault();

    await api.post(this.form.dataset.routeToSubmit!, new FormData(this.form));
  };

  private updateSubmitButtonStatus(input?: HTMLInputElement): void {
    if (!input || input.checked) {
      this.submitButton.removeAttribute('disabled');
    } else {
      this.submitButton.setAttribute('disabled', 'true');
    }
  }
}
