<template>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card" v-if="$gate.isAdmin()">
                        <div class="card-header">
                            <h3 class="card-title">Team List</h3>
                            <div class="card-tools">
                                <router-link to="/team/create" class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus-square"></i>
                                    Add New
                                </router-link>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Image</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="team in teams.data" :key="team.id">

                                        <td>{{ team.id }}</td>
                                        <td class="text-capitalize">{{ team.name }}</td>
                                        <td>{{ team.description }}</td>
                                        <td>{{ team.status }}</td>
                                        <td>
                                            <img v-if="team.image_src" :src="`upload/team/${team.image_src}`"
                                                class="profile-user-img img-fluid" style="height:60px; width:60px;" />
                                        </td>
                                        <td>
                                            <router-link :to="{ name: 'team_edit', params: { id: team.id } }"
                                                class="btn btn-primary btn-sm"><i class="fa fa-edit"></i>
                                            </router-link>
                                            <a href="#" class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <pagination :data="teams" @pagination-change-page="getResults"></pagination>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
            <div v-if="!$gate.isAdmin()">
                <not-found></not-found>
            </div>
        </div>
    </section>
</template>


<script>
export default {
    data() {
        return {
            teams: {},
        }
    },
    methods: {
        getResults(page = 1) {
            this.$Progress.start();
            axios.get('/api/team?page=' + page).then(({ data }) => (this.teams = data.data));
            this.$Progress.finish();
        },
        loadTeams() {
            if (this.$gate.isAdmin()) {
                axios.get("/api/team").then(({ data }) => (this.teams = data.data));
            }
        },
    },
    mounted() {
        console.log('Component mounted.')
    },
    created() {

        this.$Progress.start();
        this.loadTeams();
        this.$Progress.finish();
    }
}
</script>