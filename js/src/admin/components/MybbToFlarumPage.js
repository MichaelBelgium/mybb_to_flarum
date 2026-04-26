import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Switch from 'flarum/common/components/Switch';
import Button from 'flarum/common/components/Button';
import FieldSet from 'flarum/common/components/FieldSet';
import Stream from 'flarum/common/utils/Stream';
import saveSettings from 'flarum/admin/utils/saveSettings';

export default class MybbToFlarumPage extends ExtensionPage {
    oninit(vnode) {
        super.oninit(vnode);

        this.migrateAvatars = Stream(false);
        this.migrateSoftThreads = Stream(false);
        this.migrateSoftPosts = Stream(false);
        this.migrateAttachments = Stream(false);

        this.migrateThreadsPosts = Stream(true);
        this.migrateUsers = Stream(true);
        this.migrateCategories = Stream(true);
        this.migrateUserGroups = Stream(true);
    }

    content() {
        const needsPath = this.migrateAvatars() || this.migrateAttachments();

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
                                <Switch
                                    state={this.migrateUsers()}
                                    onchange={(value) => {
                                        this.migrateUsers(value);
                                        if (!value) {
                                            this.migrateAvatars(false);
                                        }
                                    }}
                                >
                                    {app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.migrate_users_label')}
                                </Switch>

                                <Switch
                                    state={this.migrateThreadsPosts()}
                                    onchange={(value) => {
                                        this.migrateThreadsPosts(value);
                                        if (!value) {
                                            this.migrateAttachments(false);
                                            this.migrateSoftPosts(false);
                                            this.migrateSoftThreads(false);
                                        }
                                    }}
                                >
                                    {app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.migrate_threadsPosts_label')}
                                </Switch>

                                <Switch
                                    state={this.migrateUserGroups()}
                                    onchange={this.migrateUserGroups}
                                >
                                    {app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.migrate_userGroups_label')}
                                </Switch>

                                <Switch
                                    state={this.migrateCategories()}
                                    onchange={this.migrateCategories}
                                >
                                    {app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.general.migrate_categories_label')}
                                </Switch>
                            </FieldSet>

                            <FieldSet label={app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.title')}>
                                <Switch
                                    state={this.migrateAvatars()}
                                    onchange={(value) => {
                                        this.migrateAvatars(value);
                                        if (value) {
                                            this.migrateUsers(true);
                                        }
                                    }}
                                >
                                    {app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_avatars_label')}
                                </Switch>

                                <Switch
                                    state={this.migrateAttachments()}
                                    onchange={(value) => {
                                        this.migrateAttachments(value);
                                        if (value) {
                                            this.migrateThreadsPosts(true);
                                        }
                                    }}
                                >
                                    {app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_attachments_label')}
                                </Switch>

                                <Switch
                                    state={this.migrateSoftThreads()}
                                    onchange={this.migrateSoftThreads}
                                >
                                    {app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_soft_threads_label')}
                                </Switch>

                                <Switch
                                    state={this.migrateSoftPosts()}
                                    onchange={this.migrateSoftPosts}
                                >
                                    {app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.options.migrate_soft_posts_label')}
                                </Switch>
                            </FieldSet>

                            <FieldSet label={app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.title')}>
                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.host_label')}</div>
                                <input className="FormControl" type="text" bidi={this.setting('mybb_host', '127.0.0.1')} />

                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.user_label')}</div>
                                <input className="FormControl" type="text" bidi={this.setting('mybb_user')} />

                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.password_label')}</div>
                                <input className="FormControl" type="password" bidi={this.setting('mybb_password')} />

                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.db_label')}</div>
                                <input className="FormControl" type="text" bidi={this.setting('mybb_db')} />

                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.prefix_label')}</div>
                                <input className="FormControl" type="text" bidi={this.setting('mybb_prefix', 'mybb_')} />

                                <div className="helpText">{app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.form.mybb.path_label')}</div>
                                <input
                                    className="FormControl"
                                    type="text"
                                    bidi={this.setting('mybb_path')}
                                    placeholder="/path/to/mybb"
                                    disabled={!needsPath}
                                />
                            </FieldSet>

                            <Button
                                className="Button Button--danger"
                                icon="fas fa-exchange-alt"
                                type="submit"
                                loading={this.loading}
                            >
                                {app.translator.trans('michaelbelgium-mybb-to-flarum.admin.content.convert_button')}
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        );
    }

    onsubmit(e) {
        e.preventDefault();
        this.loading = true;
        m.redraw();

        const host = this.setting('mybb_host', '127.0.0.1')();
        const user = this.setting('mybb_user')();
        const db = this.setting('mybb_db')();
        const path = this.setting('mybb_path')();

        let fail = false;

        if (this.migrateAvatars() && path === '') {
            alert('When migrating avatars, the mybb path cannot be empty. You need an existing mybb installation.');
            fail = true;
        }

        if (!host || !user || !db) {
            alert('MyBB host, user, and database name cannot be empty.');
            fail = true;
        }

        if (fail) {
            this.loading = false;
            m.redraw();
            return;
        }

        saveSettings({
            'mybb_host': host,
            'mybb_user': user,
            'mybb_password': this.setting('mybb_password')(),
            'mybb_db': db,
            'mybb_prefix': this.setting('mybb_prefix', 'mybb_')(),
            'mybb_path': path,
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
                    doCategories: this.migrateCategories(),
                }
            }).then(data => {
                alert(data.message);
                this.loading = false;
                m.redraw();
            }).catch(error => {
                alert(error.response?.errors?.[0]?.detail ?? 'An error occurred during migration.');
                this.loading = false;
                m.redraw();
            });
        }).catch(error => {
            alert('Failed to save settings: ' + (error.message ?? 'Unknown error'));
            this.loading = false;
            m.redraw();
        });
    }
}
