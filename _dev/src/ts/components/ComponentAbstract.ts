export abstract class ComponentAbstract {
  protected element: HTMLElement;

  public constructor(element: HTMLElement) {
    this.element = element;
  }
}
