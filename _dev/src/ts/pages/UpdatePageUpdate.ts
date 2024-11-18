import UpdatePage from './UpdatePage';
import ProgressTracker from "../components/ProgressTracker";

export default class UpdatePageUpdate extends UpdatePage {
  protected stepCode = 'update';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();

    // uncomment for dev purpose

    const progressTrackerContainer = document.querySelector('[data-component="progress-tracker"]') as HTMLDivElement;

    window.ProgressTracker = new ProgressTracker(progressTrackerContainer);
  }
}
