import ModalContainer from './components/ModalContainer';
import RouteHandler from './routing/RouteHandler';
import ScriptHandler from './routing/ScriptHandler';

export const routeHandler = new RouteHandler();

export const scriptHandler = new ScriptHandler();

new ModalContainer().init();

export default { routeHandler, scriptHandler };
