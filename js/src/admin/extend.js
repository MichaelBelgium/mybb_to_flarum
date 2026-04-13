import Extend from 'flarum/common/extenders';
import MybbToFlarumPage from './components/MybbToFlarumPage';

export default [
    new Extend.Admin()
        .page(MybbToFlarumPage)
];