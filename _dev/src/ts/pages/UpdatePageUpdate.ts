import UpdatePage from './UpdatePage';
import ProgressTracker from '../components/ProgressTracker';
import { ApiResponseAction } from '../types/apiTypes';
import Process from '../utils/Process';
import api from '../api/RequestHandler';

export default class UpdatePageUpdate extends UpdatePage {
  protected stepCode = 'update';
  #progressTracker: ProgressTracker = new ProgressTracker(this.#progressTrackerContainer);
  #restoreAlertForm: null | HTMLFormElement = null;
  #restoreButtonForm: null | HTMLFormElement = null;

  constructor() {
    super();
  }

  public mount = async () => {
    this.initStepper();

    const stepContent = document.getElementById('ua_step_content')!;
    const updateAction = stepContent.dataset.initialProcessAction!;

    const process = new Process({
      onProcessResponse: this.#onProcessResponse,
      onProcessEnd: this.#onProcessEnd,
      onError: this.#onError
    });

    await process.startProcess(updateAction);
  };

  public beforeDestroy = () => {
    this.#progressTracker.beforeDestroy();

    this.#restoreAlertForm?.removeEventListener('submit', this.#handleRestoreSubmit);
    this.#restoreButtonForm?.removeEventListener('submit', this.#handleRestoreSubmit);
  };

  get #progressTrackerContainer(): HTMLDivElement {
    const progressTrackerContainer = document.querySelector(
      '[data-component="progress-tracker"]'
    ) as HTMLDivElement;

    if (!progressTrackerContainer) {
      throw new Error('Progress tracker container not found');
    }

    return progressTrackerContainer;
  }

  #onProcessResponse = (response: ApiResponseAction): void => {
    this.#progressTracker.updateProgress(response);
  };

  #onProcessEnd = async (response: ApiResponseAction): Promise<void> => {
    if (response.error) {
      this.#onError(response);
    } else {
      await api.post(this.#progressTrackerContainer.dataset.successRoute!);
    }
  };

  #onError = (response: ApiResponseAction): void => {
    this.#progressTracker.updateProgress(response);
    this.#progressTracker.endProgress();
    this.#displayErrorAlert();
    this.#displayErrorButtons();
  };

  #displayErrorAlert = () => {
    const alertContainer = document.getElementById('error-alert');

    if (!alertContainer) {
      throw new Error('Error alert container not found');
    }

    alertContainer.classList.remove('hidden');

    this.#restoreAlertForm = document.forms.namedItem('restore-alert');
    this.#restoreAlertForm?.addEventListener('submit', this.#handleRestoreSubmit);
  };

  #displayErrorButtons = () => {
    const buttonsContainer = document.getElementById('error-buttons');

    if (!buttonsContainer) {
      throw new Error('Error buttons container not found');
    }

    buttonsContainer.classList.remove('hidden');

    this.#restoreButtonForm = document.forms.namedItem('restore-button');
    this.#restoreButtonForm?.addEventListener('submit', this.#handleRestoreSubmit);
  };

  #handleRestoreSubmit = async (event: SubmitEvent) => {
    event.preventDefault();

    const form = event.target as HTMLFormElement;

    await api.post(form.dataset.routeToSubmit!);
  };
}
