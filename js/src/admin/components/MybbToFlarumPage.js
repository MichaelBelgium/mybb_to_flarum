import Page from 'flarum/components/Page';
import Switch from 'flarum/components/Switch';
import Button from 'flarum/components/Button';
import FieldSet from 'flarum/components/FieldSet';

export default class MybbToFlarumPage extends Page {
	init() {
		super.init();
		
		this.migrateAvatars = m.prop(false);
		this.migrateSoftThreads = m.prop(false);
		this.migrateSoftPosts = m.prop(false);

		this.migrateThreadsPosts = m.prop(true);
		this.migrateUsers = m.prop(true);
		this.migrateCategories = m.prop(true);
		this.migrateUserGroups = m.prop(true);

		this.mybb = {
			host: m.prop(''),
			user: m.prop(''),
			db: m.prop(''),
			prefix: m.prop(''),
			password: m.prop(''),
			mybbPath: m.prop('')
		};
	}

  	view() {
		return (
			<div className="mybbtoflarumPage">
				<div className="mybbtoflarumPage-header">
					<div className="container">
						<p>
							{app.translator.trans('mybbtoflarum.admin.page.text')}
						</p>
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
												$("input[name=mybbPath]").show();
											else
												$("input[name=mybbPath]").hide();
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
									<input className="FormControl" type="text" placeholder={app.translator.trans('mybbtoflarum.admin.page.form.mybb.host')} bidi={this.mybb.host} />,
									<input className="FormControl" type="text" placeholder={app.translator.trans('mybbtoflarum.admin.page.form.mybb.user')} bidi={this.mybb.user} />,
									<input className="FormControl" type="password" placeholder={app.translator.trans('mybbtoflarum.admin.page.form.mybb.password')} bidi={this.mybb.password} />,
									<input className="FormControl" type="text" placeholder={app.translator.trans('mybbtoflarum.admin.page.form.mybb.db')} bidi={this.mybb.db} />,
									<input className="FormControl" type="text" placeholder={app.translator.trans('mybbtoflarum.admin.page.form.mybb.prefix')} bidi={this.mybb.prefix} />,
									<input className="FormControl" type="text" placeholder={app.translator.trans('mybbtoflarum.admin.page.form.mybb.mybbPath')} bidi={this.mybb.mybbPath} name="mybbPath" style="display: none;" />
								]
							})}

							{FieldSet.component({
								label: app.translator.trans('mybbtoflarum.admin.page.form.options.title'),
								children: [
									Switch.component({
										state: this.migrateUsers(),
										onchange: this.migrateUsers,
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
		console.log(this.migrateAvatars());
		console.log(this.migrateSoftThreads());
		console.log(this.migrateSoftPosts());

		console.log(this.migrateThreadsPosts());
		console.log(this.migrateUsers());
		console.log(this.migrateCategories());
		console.log(this.migrateUserGroups());

		console.log(this.mybb.host());
		console.log(this.mybb.user());
		console.log(this.mybb.db());
		console.log(this.mybb.prefix());
		console.log(this.mybb.password());
		// this.loading = true;

		location.reload();
	}
}