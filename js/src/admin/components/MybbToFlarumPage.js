import ExtensionPage from 'flarum/components/ExtensionPage';
import Switch from 'flarum/components/Switch';
import Button from 'flarum/components/Button';
import FieldSet from 'flarum/components/FieldSet';
import saveSettings from 'flarum/utils/saveSettings';
import Stream from 'flarum/utils/Stream';

export default class MybbToFlarumPage extends ExtensionPage {
    oninit(vnode) {
        super.oninit(vnode);
        
        this.migrateAvatars = Stream(true);
        this.migrateSoftThreads = Stream(false);
        this.migrateSoftPosts = Stream(false);

        this.migrateThreadsPosts = Stream(true);
        this.migrateUsers = Stream(true);
        this.migrateCategories = Stream(true);
        this.migrateUserGroups = Stream(true);

        this.mybb = {
            host: Stream(typeof app.data.settings.mybb_host === 'undefined' ? '127.0.0.1' : app.data.settings.mybb_host),
            user: Stream(typeof app.data.settings.mybb_user === 'undefined' ? '' : app.data.settings.mybb_user),
            db: Stream(typeof app.data.settings.mybb_db === 'undefined' ? '' : app.data.settings.mybb_db),
            prefix: Stream(typeof app.data.settings.mybb_prefix === 'undefined' ? 'mybb_' : app.data.settings.mybb_prefix),
            password: Stream(typeof app.data.settings.mybb_password === 'undefined' ? '' : app.data.settings.mybb_password),
            mybbPath: Stream(typeof app.data.settings.mybb_path === 'undefined' ? '' : app.data.settings.mybb_path)
        };
    }

    content() {
        return (
            <div className="mybbtoflarumPage">
                <div className="mybbtoflarumPage-header">
                    <div className="container">
                        {app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.description_text')}
                    </div>
                </div>
                <div className="mybbtoflarumPage-content">
                    <div className="container">
                        <form onsubmit={this.onsubmit.bind(this)}>
                            <FieldSet label={app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.title')}>
                                {[Switch.component({
                                    state: this.migrateAvatars(),
                                    onchange: (value) => {
                                        this.migrateAvatars(value);

                                        if(value)
                                        {
                                            this.migrateUsers(value);
                                            $("input[name=mybbPath]").removeAttr("disabled");
                                        }
                                        else
                                            $("input[name=mybbPath]").attr("disabled", "disabled");
                                    }
                                }, app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.migrate_avatars_label')),
                                
                                Switch.component({
                                    state: this.migrateAttachments(),
                                    onchange: (value) => {
                                        this.migrateAttachments(value);

                                        if(value)
                                        {
                                            this.migrateThreadsPosts(value);
                                            $("input[name=mybbPath]").removeAttr("disabled");
                                        }
                                        else
                                            $("input[name=mybbPath]").attr("disabled", "disabled");
                                    },
                                }, app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_attachments_label')),

                                Switch.component({
                                    state: this.migrateSoftThreads(),
                                    onchange: this.migrateSoftThreads
                                }, app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.migrate_soft_threads_label')),

                                Switch.component({
                                    state: this.migrateSoftPosts(),
                                    onchange: this.migrateSoftPosts
                                }, app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.migrate_soft_posts_label'))
                            ]}
                            </FieldSet>

                            <FieldSet label={app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.title')}>
                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.host_label')}</div>
                                <input className="FormControl" type="text" bidi={this.mybb.host} value={this.mybb.host()} />
                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.user_label')}</div>
                                <input className="FormControl" type="text" bidi={this.mybb.user} />
                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.password_label')}</div>
                                <input className="FormControl" type="password" bidi={this.mybb.password} />
                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.db_label')}</div>
                                <input className="FormControl" type="text" bidi={this.mybb.db} />
                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.prefix_label')}</div>
                                <input className="FormControl" type="text" bidi={this.mybb.prefix} value={this.mybb.prefix()} />
                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.path_label')}</div>
                                <input className="FormControl" type="text" bidi={this.mybb.mybbPath} name="mybbPath" placeholder="/path/to/mybb" />
                            </FieldSet>

                            <FieldSet label={app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.title')}>
                            {[
                                Switch.component({
                                    state: this.migrateUsers(),
                                    onchange: (value) => {
                                        this.migrateUsers(value);

                                        if(!value) {
                                            this.migrateAvatars(value);
                                            $("input[name=mybbPath]").attr("disabled", "disabled");
                                        }
                                    }
                                }, app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_users_label')),

                                Switch.component({
                                    state: this.migrateThreadsPosts(),
                                    onchange: (value) => {
                                        this.migrateThreadsPosts(value);

                                        if(!value) {
                                            this.migrateAttachments(value);
                                            this.migrateSoftPosts(value);
                                            this.migrateSoftThreads(value);
                                            $("input[name=mybbPath]").attr("disabled", "disabled");
                                        }
                                    },
                                }, app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_threadsPosts_label')),

                                Switch.component({
                                    state: this.migrateUserGroups(),
                                    onchange: this.migrateUserGroups,
                                }, app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_userGroups_label')),

                                Switch.component({
                                    state: this.migrateCategories(),
                                    onchange: this.migrateCategories,
                                }, app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_categories_label'))
                            ]}
                            </FieldSet>

                            {Button.component(
                                {
                                    className: 'Button Button--danger',
                                    icon: 'fas fa-exchange-alt',
                                    type: 'submit',
                                    loading: this.loading
                                }, 
                                app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.convert_button')
                            )}
                        </form>
                    </div>
                </div>
            </div>
        );
    }
    
    onsubmit(e) {
        e.preventDefault();
        this.loading = true;

        var fail = false;

        if(this.migrateAvatars() && this.mybb.mybbPath() === '')
        {
            alert('When migrating avatars, the mybb path can not be empty. You need an exisitng mybb installation.');
            fail = true;
        }

        Object.keys(this.mybb).forEach(key => {
            if(key !== 'mybbPath' && key !== 'prefix' && this.mybb[key]() === '')
            {
                alert('Mybb: ' + key + ' can not be empty');
                fail = true;
            }
        })

        if(fail)
        {
            this.loading = false;
            return;
        }

        saveSettings({
            'mybb_host': this.mybb.host(),
            'mybb_user': this.mybb.user(),
            'mybb_password': this.mybb.password(),
            'mybb_db': this.mybb.db(),
            'mybb_prefix': this.mybb.prefix(),
            'mybb_path': this.mybb.mybbPath()
        }).then(() => {
            app.request({
                method: 'POST',
                url: app.forum.attribute('apiUrl') + '/mybb-to-flarum',
                body: {
                    avatars: this.migrateAvatars(),
                    softposts: this.migrateSoftPosts(),
                    softthreads: this.migrateSoftThreads(),
                    attachments: this.migrateAttachments(),
                    doUsers: this.migrateUsers(),
                    doThreadsPosts: this.migrateThreadsPosts(),
                    doGroups: this.migrateUserGroups(),
                    doCategories: this.migrateCategories()
                }
            }).then(data => alert(data.message));
        });

        this.loading = false;
    }
}
