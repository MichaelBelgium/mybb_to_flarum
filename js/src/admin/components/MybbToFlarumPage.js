import Page from 'flarum/components/Page';
import MybbToFlarumSettingsModal from './MybbToFlarumSettingsModal';
import Button from 'flarum/components/Button';

export default class MybbToFlarumPage extends Page {
  view() {
    return (
		<div className="mybbtoflarumPage">
			<div className="container">
			<p>
				{app.translator.trans('mybbtoflarum.admin.page.text')}
			</p>
			{Button.component({
				className: 'Button Button--primary',
				children: app.translator.trans('mybbtoflarum.admin.page.btnSettings'),
				onclick: () => app.modal.show(new MybbToFlarumSettingsModal())
			})}
			{Button.component({
				className: 'Button Button--danger',
				icon: 'fas fa-exchange-alt',
				children: app.translator.trans('mybbtoflarum.admin.page.btnConvert'),
				onclick: () => console.log(app.extensionSettings)
			})}
			</div>
		</div>
    );
  }
}