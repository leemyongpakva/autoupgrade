import Hydration from '../../src/ts/utils/Hydration';
import { ApiResponseHydration } from '../../src/ts/types/apiTypes';
import RouteHandler from '../../src/ts/routing/RouteHandler';
import ScriptHandler from '../../src/ts/routing/ScriptHandler';

const setNewRouteMock = jest.spyOn(RouteHandler.prototype, 'setNewRoute');
const updateRouteScriptMock = jest.spyOn(ScriptHandler.prototype, 'updateRouteScript');
const unloadRouteScriptMock = jest.spyOn(ScriptHandler.prototype, 'unloadRouteScript');

jest.mock('../../src/ts/pages/HomePage', () => {
  return jest.fn().mockImplementation(() => {
    return {
      mount: () => {},
      beforeDestroy: () => {}
    };
  });
});

jest.mock('../../src/ts/pages/UpdatePageBackup', () => {
  return jest.fn().mockImplementation(() => ({
    mount: () => {},
    beforeDestroy: () => {
      const element = document.getElementById('my_paragraph');
      if (!element) {
        throw new Error(
          'Script unloaded too late, the element has already been removed from the DOM'
        );
      }
    }
  }));
});

describe('Hydration', () => {
  let hydration: Hydration;

  beforeEach(() => {
    hydration = new Hydration();
    document.body.innerHTML = `
      <div id="parent">
        <p>Old Content</p>
      </div>
    `;
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  it('should update the innerHTML of the target element', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: undefined
    };

    hydration.hydrate(response);

    const updatedElement = document.getElementById('parent');
    expect(updatedElement!.innerHTML).toBe('<p>New Content</p>');
  });

  it('should call scriptHandler.updateRouteScript when new_route is provided', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: 'new_route_value'
    };

    hydration.hydrate(response);

    expect(updateRouteScriptMock).toHaveBeenCalledWith('new_route_value');
  });

  it('should call routeHandler.setNewRoute when new_route is provided and fromPopState is false', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: 'new_route_value'
    };

    hydration.hydrate(response);

    expect(setNewRouteMock).toHaveBeenCalledWith('new_route_value');
  });

  it('should not call routeHandler.setNewRoute when fromPopState is true', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: 'new_route_value'
    };

    hydration.hydrate(response, true);

    expect(setNewRouteMock).not.toHaveBeenCalled();
  });

  it('should not update the content if the element does not exist', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'non_existent_id'
    };

    hydration.hydrate(response);

    const updatedElement = document.getElementById('parent');
    expect(updatedElement!.innerHTML).toBe(`
        <p>Old Content</p>
      `);
  });

  it('should dispatch the hydration event on the updated element', () => {
    const response: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: undefined
    };

    const updatedElement = document.getElementById('parent');
    const dispatchEventSpy = jest.spyOn(updatedElement!, 'dispatchEvent');

    hydration.hydrate(response);

    expect(dispatchEventSpy).toHaveBeenCalledWith(
      expect.objectContaining({
        type: Hydration.hydrationEventName
      })
    );
  });
});

describe('Hydration and scripts lifecycle', () => {
  let hydration: Hydration;

  beforeEach(() => {
    hydration = new Hydration();
    document.body.innerHTML = `
      <div id="parent">
        <p>Old Content</p>
      </div>
    `;
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  it('should unload the current script safely before loading the next one', () => {
    const initialResponse: ApiResponseHydration = {
      hydration: true,
      new_content: `<p id="my_paragraph">Old Content</p>`,
      parent_to_update: 'parent',
      new_route: 'update-page-backup'
    };
    hydration.hydrate(initialResponse);

    expect(setNewRouteMock).toHaveBeenCalledTimes(1);
    expect(unloadRouteScriptMock).toHaveBeenCalledTimes(1);

    const nextResponse: ApiResponseHydration = {
      hydration: true,
      new_content: `<p>New Content</p>`,
      parent_to_update: 'parent',
      new_route: 'home-page'
    };
    hydration.hydrate(nextResponse);

    expect(setNewRouteMock).toHaveBeenCalledTimes(2);
    expect(unloadRouteScriptMock).toHaveBeenCalledTimes(2);
  });
});
