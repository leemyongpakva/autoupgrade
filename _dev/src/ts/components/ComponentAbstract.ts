export abstract class ComponentAbstract {
  protected element: HTMLElement;

  public constructor(element: HTMLElement) {
    this.element = element;
  }

  protected queryElement = <T extends HTMLElement>(selector: string, errorMessage: string): T => {
    const element = (this.element.querySelector(selector) as T) ?? document.querySelector(selector);
    if (!element) throw new Error(errorMessage);
    return element;
  };
}
