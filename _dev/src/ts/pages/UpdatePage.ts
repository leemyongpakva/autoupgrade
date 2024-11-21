import Stepper from '../utils/Stepper';
import PageAbstract from './PageAbstract';

export default class UpdatePage extends PageAbstract {
  protected stepCode = 'version-choice';

  constructor() {
    super();
  }

  public mount() {}

  public beforeDestroy() {}

  protected initStepper = () => {
    if (!window.UpdatePageStepper) {
      window.UpdatePageStepper = new Stepper();
    } else {
      window.UpdatePageStepper.setCurrentStep(this.stepCode);
    }
  };
}
