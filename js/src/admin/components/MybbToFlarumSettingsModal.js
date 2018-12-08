import SettingsModal from 'flarum/components/SettingsModal';

export default class MybbToFlarumSettingsModal extends SettingsModal {
    className() {
        return 'Modal--small';
    }

    title() {
        return app.translator.trans('acme-helloworld.admin.settings.title');
    }

    form() {
        return [
            <div className="Form-group">
                <label>{app.translator.trans('acme-helloworld.admin.settings.firstSetting')}</label>
                <input className="FormControl" bidi={this.setting('acme.helloworld.firstSetting')}/>
            </div>,

            <div className="Form-group">
                <label>{app.translator.trans('acme-helloworld.admin.settings.secondSetting')}</label>
                <input className="FormControl" bidi={this.setting('acme.helloworld.secondSetting')}/>
            </div>,
        ];
    }
}