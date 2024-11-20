import ModalContainer from './components/ModalContainer';
import RouteHandler from './routing/RouteHandler';
import ScriptHandler from './routing/ScriptHandler';

export const routeHandler = new RouteHandler();

export const scriptHandler = new ScriptHandler();
export const modalContainer = new ModalContainer();

export default { routeHandler, scriptHandler, modalContainer };
