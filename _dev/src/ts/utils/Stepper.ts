import type { Step } from '../types/Stepper';

export default class Stepper {
  private stepper: HTMLDivElement;
  private steps: Step[];

  private baseClass = 'stepper__step';
  private currentClass = `${this.baseClass}--current`;
  private doneClass = `${this.baseClass}--done`;
  private normalClass = `${this.baseClass}--normal`;

  /**
   * @constructor
   * @throws Will throw an error if the stepper or its steps are not found in the DOM.
   * @description Initializes the Stepper by finding the parent element in the DOM and setting up the steps.
   */
  constructor() {
    const stepper = document.getElementById(
      window.AutoUpgradeVariables.stepper_parent_id
    ) as HTMLDivElement | null;
    if (!stepper) {
      throw new Error("The stepper wasn't found inside DOM. stepper can't be initiated properly");
    }

    this.stepper = stepper;

    const domSteps = Array.from(this.stepper.children) as HTMLElement[];

    if (!domSteps.length) {
      throw new Error("The stepper hasn't steps inside DOM. stepper can't be initiated properly");
    }

    this.steps = domSteps.map((step) => {
      const stepCode = step.dataset.stepCode;
      if (!stepCode) {
        throw new Error(
          "Step code is missing in one of the steps. stepper can't be initiated properly"
        );
      }
      return {
        code: stepCode,
        element: step
      };
    });
  }

  /**
   * @public
   * @param {string} currentStep - The code of the current step to be set.
   * @description Sets the current step in the stepper and updates the classes for each step accordingly.
   */
  public setCurrentStep = (currentStep: string) => {
    const stepIndex = this.steps.findIndex((step) => step.code === currentStep);

    if (stepIndex === -1) {
      console.debug(`Step ${currentStep} not found in list.`);
      return;
    }
    this.stepper.classList.add('stepper--hydration');

    this.steps.forEach((step, i) => {
      const { element } = step;

      const newClass = this.#getClassOfStep(stepIndex, i);

      if (!element.classList.contains(newClass)) {
        element.classList.remove(this.currentClass, this.doneClass, this.normalClass);
        element.classList.add(newClass);
      }
    });
  };

  #getClassOfStep(referenceIndex: number, currentIndex: number): string {
    if (currentIndex === referenceIndex) {
      return this.currentClass;
    }

    if (currentIndex < referenceIndex) {
      return this.doneClass;
    }

    return this.normalClass;
  }
}
