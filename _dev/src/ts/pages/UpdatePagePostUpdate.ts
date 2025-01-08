import StepPage from './StepPage';

export default class UpdatePagePostUpdate extends StepPage {
  protected stepCode = 'post-update';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();
  }
}
