import { extend } from 'flarum/extend';
import app from 'flarum/app';
import AdminNav from 'flarum/components/AdminNav';
import AdminLinkButton from 'flarum/components/AdminLinkButton';
import MybbToFlarumPage from './components/MybbToFlarumPage';

app.initializers.add('michaelbelgium-mybb-to-flarum', () => {
    app.routes.mybbtoflarum = {path: '/mybb-to-flarum', component: MybbToFlarumPage };

    extend(AdminNav.prototype, 'items', items => {
        items.add('pages', AdminLinkButton.component(
            {
                href: app.route('mybbtoflarum'),
                icon: 'fas fa-exchange-alt',
                description: app.translator.trans('michaelbelgium-mybb-to-flarum.admin.nav.description_text')
            }, 
            app.translator.trans('michaelbelgium-mybb-to-flarum.admin.nav.title')
        ));
    });
});