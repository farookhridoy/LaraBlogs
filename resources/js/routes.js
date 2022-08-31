export default [
    { path: '/dashboard', component: require('./components/Dashboard.vue').default },
    { path: '/profile', component: require('./components/auth/Profile.vue').default },
    { path: '/developer', component: require('./components/Developer.vue').default },
    { path: '/users', component: require('./components/auth/Users.vue').default },
    { path: '/blogs', component: require('./components/product/Products.vue').default },
    { path: '/product/tag', component: require('./components/product/Tag.vue').default },
    { path: '/product/category', component: require('./components/product/Category.vue').default },

    //teams
    { path: '/team', component: require('./components/team/Index.vue').default },
    { name: 'team_create', path: '/team/create', component: require('./components/team/Create.vue').default },
    { name: 'team_edit', path: '/team/edit/:id', component: require('./components/team/Edit.vue').default },

    //if not found then redirect to not found
    { path: '*', component: require('./components/NotFound.vue').default }
];
