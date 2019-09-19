import Page from 'flarum/components/Page';
import Switch from 'flarum/components/Switch';
import Button from 'flarum/components/Button';
import FieldSet from 'flarum/components/FieldSet';
import saveSettings from 'flarum/utils/saveSettings';

export default class MybbToFlarumPage extends Page {
	init() {
		super.init();
		
		this.migrateAvatars = m.prop(true);
		this.migrateSoftThreads = m.prop(false);
		this.migrateSoftPosts = m.prop(false);

		this.migrateThreadsPosts = m.prop(true);
		this.migrateUsers = m.prop(true);
		this.migrateCategories = m.prop(true);
		this.migrateUserGroups = m.prop(true);

		this.mybb = {
			host: m.prop(typeof app.data.settings.mybb_host === 'undefined' ? '127.0.0.1' : app.data.settings.mybb_host),
			user: m.prop(typeof app.data.settings.mybb_user === 'undefined' ? '' : app.data.settings.mybb_user),
			db: m.prop(typeof app.data.settings.mybb_db === 'undefined' ? '' : app.data.settings.mybb_db),
			prefix: m.prop(typeof app.data.settings.mybb_prefix === 'undefined' ? 'mybb_' : app.data.settings.mybb_prefix),
			password: m.prop(typeof app.data.settings.mybb_password === 'undefined' ? '' : app.data.settings.mybb_password),
			mybbPath: m.prop(typeof app.data.settings.mybb_path === 'undefined' ? '' : app.data.settings.mybb_path)
		};
	}

  	view() {
		return (
			<div className="mybbtoflarumPage">
				<div className="mybbtoflarumPage-header">
					<div className="container">
						{app.translator.trans('mybbtoflarum.admin.page.text')}
					</div>
				</div>
				<div className="mybbtoflarumPage-content">
					<div className="container">
						<form onsubmit={this.onsubmit.bind(this)}>
							{FieldSet.component({
								label: app.translator.trans('mybbtoflarum.admin.page.form.general.title'),
								children: [
									Switch.component({
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
										},
										children: app.translator.trans('mybbtoflarum.admin.page.form.general.migrateAvatars'),
									}),
									Switch.component({
										state: this.migrateSoftThreads(),
										onchange: this.migrateSoftThreads,
										children: app.translator.trans('mybbtoflarum.admin.page.form.general.migrateSoftThreads'),
									}),
									Switch.component({
										state: this.migrateSoftPosts(),
										onchange: this.migrateSoftPosts,
										children: app.translator.trans('mybbtoflarum.admin.page.form.general.migrateSoftPosts'),
									})
								]
							})}

							{FieldSet.component({
								label: app.translator.trans('mybbtoflarum.admin.page.form.mybb.title'),
								children: [
									<div className="helpText">{app.translator.trans('mybbtoflarum.admin.page.form.mybb.host')}</div>,
									<input className="FormControl" type="text" bidi={this.mybb.host} value={this.mybb.host()} />,
									<div className="helpText">{app.translator.trans('mybbtoflarum.admin.page.form.mybb.user')}</div>,
									<input className="FormControl" type="text" bidi={this.mybb.user} />,
									<div className="helpText">{app.translator.trans('mybbtoflarum.admin.page.form.mybb.password')}</div>,
									<input className="FormControl" type="password" bidi={this.mybb.password} />,
									<div className="helpText">{app.translator.trans('mybbtoflarum.admin.page.form.mybb.db')}</div>,
									<input className="FormControl" type="text" bidi={this.mybb.db} />,
									<div className="helpText">{app.translator.trans('mybbtoflarum.admin.page.form.mybb.prefix')}</div>,
									<input className="FormControl" type="text" bidi={this.mybb.prefix} value={this.mybb.prefix()} />,
									<div className="helpText">{app.translator.trans('mybbtoflarum.admin.page.form.mybb.mybbPath')}</div>,
									<input className="FormControl" type="text" bidi={this.mybb.mybbPath} name="mybbPath" placeholder="/path/to/mybb" />
								]
							})}

							{FieldSet.component({
								label: app.translator.trans('mybbtoflarum.admin.page.form.options.title'),
								children: [
									Switch.component({
										state: this.migrateUsers(),
										onchange: (value) => {
											this.migrateUsers(value);

											if(!value)
												this.migrateAvatars(value);
										},
										children: app.translator.trans('mybbtoflarum.admin.page.form.options.migrateUsers'),
									}),
									Switch.component({
										state: this.migrateThreadsPosts(),
										onchange: this.migrateThreadsPosts,
										children: app.translator.trans('mybbtoflarum.admin.page.form.options.migrateThreadsPosts'),
									}),
									Switch.component({
										state: this.migrateUserGroups(),
										onchange: this.migrateUserGroups,
										children: app.translator.trans('mybbtoflarum.admin.page.form.options.migrateUserGroups'),
									}),
									Switch.component({
										state: this.migrateCategories(),
										onchange: this.migrateCategories,
										children: app.translator.trans('mybbtoflarum.admin.page.form.options.migrateCategories'),
									})
								]
							})}

							{Button.component({
								className: 'Button Button--danger',
								icon: 'fas fa-exchange-alt',
								type: 'submit',
								children: app.translator.trans('mybbtoflarum.admin.page.btnConvert'),
								loading: this.loading
							})}
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
			'mybb_path': (this.mybb.mybbPath().endsWith('/') ? this.mybb.mybbPath() : this.mybb.mybbPath() + '/')
		}).then(() => {
			app.request({
				method: 'POST',
				url: app.forum.attribute('apiUrl') + '/mybb-to-flarum',
				data: {
					avatars: this.migrateAvatars(),
					softposts: this.migrateSoftPosts(),
					softthreads: this.migrateSoftThreads(),
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
