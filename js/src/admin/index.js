import app from 'flarum/app';
import MybbToFlarumPage from './components/MybbToFlarumPage';

app.initializers.add('michaelbelgium-mybb-to-flarum', () => {
    app.extensionData.for('michaelbelgium-mybb-to-flarum').registerPage(MybbToFlarumPage);
});