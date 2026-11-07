import GlobalView from './components/global/Index';

// Platform and template views
import Platforms from './components/views/Platforms';
import Settings from './components/views/Settings';
import AllTemplates from './components/views/AllTemplates';

// Settings views
import FeedSettings from './components/views/settings/feeds/FeedSettings';
import GlobalFeedSettings from './components/views/settings/feeds/GlobalFeedSettings';
import Shoppable from './components/views/settings/shoppable/Shoppable';
import AdvanceSettings from './components/views/settings/advance/Advance';

// Instagram template editor
import EditInstagramFeedView from './components/feeds/editor/InstagramView';

const globalSettingsViewChildrenRoutes = [
    {
        path: 'global-feeds-settings',
        name: 'global-feeds-settings',
        component: GlobalFeedSettings,
        meta: { title: 'Global Feed Settings' }
    },
    {
        path: 'instagram-settings',
        name: 'instagram-settings',
        component: FeedSettings,
        meta: { title: 'Instagram Settings' }
    },
    {
        path: 'shoppable-by-hashtags',
        name: 'shoppable-by-hashtags',
        component: Shoppable,
        meta: { title: 'Shoppable by Hashtags' }
    },
    {
        path: 'advance-settings',
        name: 'advance-settings',
        component: AdvanceSettings,
        meta: { title: 'Advance Settings' }
    },
];

export const routes = [
    {
        path: '/',
        component: GlobalView,
        props: true,
        children: [
            {
                path: '/',
                name: 'platforms',
                component: Platforms,
                meta: { title: 'Instagram Feed' }
            },
            {
                path: 'templates',
                name: 'templates',
                component: AllTemplates,
                meta: { title: 'Templates' }
            },
            {
                path: '/settings/',
                component: Settings,
                children: globalSettingsViewChildrenRoutes,
                meta: { title: 'Settings' }
            },
        ]
    },
    {
        path: '/edit-instagram-template/:template_id',
        name: 'edit-instagram-template',
        component: EditInstagramFeedView,
        meta: { title: 'Edit Instagram Template' }
    },
];
