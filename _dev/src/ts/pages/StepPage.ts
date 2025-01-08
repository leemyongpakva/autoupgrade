import Stepper from '../utils/Stepper';
import PageAbstract from './PageAbstract';

export default class StepPage extends PageAbstract {
  protected stepCode = '';

  constructor() {
    super();
  }

  public mount() {}

  public beforeDestroy() {}

  protected initStepper = () => {
    if (!window.PageStepper) {
      window.PageStepper = new Stepper();
    } else {
      window.PageStepper.setCurrentStep(this.stepCode);
    }
  };
}
