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
			password: m.prop('')
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
								label: 'General settings',
								children: [
									Switch.component({
										state: this.migrateAvatars(),
										onchange: this.migrateAvatars,
										children: 'Migrate avatars',
									}),
									Switch.component({
										state: this.migrateSoftThreads(),
										onchange: this.migrateSoftThreads,
										children: 'Migrate soft deleted threads',
									}),
									Switch.component({
										state: this.migrateSoftPosts(),
										onchange: this.migrateSoftPosts,
										children: 'Migrate soft deleted posts',
									}),
								]
							})}

							{FieldSet.component({
								label: 'MyBB database connection',
								children: [
									<input className="FormControl" type="text" placeholder="Host" bidi={this.mybb.host} />,
									<input className="FormControl" type="text" placeholder="User" bidi={this.mybb.user} />,
									<input className="FormControl" type="password" placeholder="Password" bidi={this.mybb.password} />,
									<input className="FormControl" type="text" placeholder="Database" bidi={this.mybb.db}/>,
									<input className="FormControl" type="text" placeholder="Table prefix" bidi={this.mybb.prefix}/>
								]
							})}

							{FieldSet.component({
								label: 'General migrate options',
								children: [
									Switch.component({
										state: this.migrateUsers(),
										onchange: this.migrateUsers,
										children: 'Migrate users',
									}),
									Switch.component({
										state: this.migrateThreadsPosts(),
										onchange: this.migrateThreadsPosts,
										children: 'Migrate threads and posts',
									}),
									Switch.component({
										state: this.migrateUserGroups(),
										onchange: this.migrateUserGroups,
										children: 'Migrate user groups',
									}),
									Switch.component({
										state: this.migrateCategories(),
										onchange: this.migrateCategories,
										children: 'Migrate categories',
									}),
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