import UpdatePage from './UpdatePage';
import ProgressTracker from "../components/ProgressTracker";

export default class UpdatePageUpdate extends UpdatePage {
  protected stepCode = 'update';

  constructor() {
    super();
  }

  public async mount() {
    this.initStepper();

    // uncomment for dev purpose

    const step = 'UpdateInitialization';

    const progressTrackerContainer = document.querySelector('[data-component="progress-tracker"]') as HTMLDivElement;

    const progressTracker = new ProgressTracker(progressTrackerContainer);

    await progressTracker.launchAction(step);
  }
}
