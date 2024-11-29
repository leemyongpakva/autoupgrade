import UpdatePage from './UpdatePage';
import ProgressTracker from '../components/ProgressTracker';
import { ApiResponseAction } from '../types/apiTypes';
import Process from '../utils/Process';
import api from '../api/RequestHandler';

export default class UpdatePageUpdate extends UpdatePage {
  protected stepCode = 'update';
  #progressTracker: ProgressTracker = new ProgressTracker(this.#progressTrackerContainer);

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
      this.#progressTracker.endProgress();
    } else {
      await api.post(this.#progressTrackerContainer.dataset.successRoute!);
    }
  };

  #onError = (response: ApiResponseAction): void => {
    this.#progressTracker.updateProgress(response);
    this.#progressTracker.endProgress();
  };
}
