<template>
  <div class="container">
    <div 
      v-if="error" 
      class="modal-dialog" 
      role="document"
    >
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Waarschuwing</h5>
        </div>
        <div class="modal-body">
          Je hebt waarschijnlijk nog geen account hier.
          Neem contact op met je studieloopbaanbegeleider
        </div>
      </div>
    </div>
    <span v-else>
      <studentform 
        :student-info="student" 
        :is-student="true"
      />
      <hr>
      <div class="text-center mb-5 d-print-none">
        <a 
          class="btn btn-primary btn-lg active" 
          role="button" 
          aria-pressed="true"
          @click="showModules = true"
        >
          Modules
        </a>
        <a 
          class="btn btn-primary btn-lg active" 
          role="button" 
          aria-pressed="true"
          @click="showModules = false"
        >
          Statistieken
        </a>
      </div>
      <div v-if="showModules">
        <div class="row">
          <div class="col-2 my-auto">
            <h4 class="text-center">Naam</h4>
          </div>      
          <div class="col-2 my-auto">
            <h4 class="text-center">Duur</h4>
          </div>
          <div class="col-2 my-auto">
            <h4 class="text-center">Begindatum</h4>
          </div>
          <div class="col-2 my-auto">
            <h4 class="text-center">Einddatum</h4>
          </div>
          <div class="col-2 my-auto">
            <h4 class="text-center">Beoordeling</h4>
          </div>
          <div class="col-2 my-auto">
            <h4 class="text-center">&nbsp;</h4>
          </div>
        </div>
        <div 
          v-for="module in moduleList[0].modules" 
          v-if="module" 
          :key="module.id" 
          class="row pt-3 pb-3" 
          style="border-bottom: 1px solid #ccc;"
        >
          <div class="col-2 my-auto">
            <div class="card bg-light">
              <div class="card-header text-center p-2">{{ module.name }}</div>
              <div class="card-body p-2">
                <p class="card-text text-center">{{ module.sub_description }}</p>
              </div>
              <div 
                v-if="module.long_description" 
                class="card-footer bg-transparent border-success"
              >
                <a 
                  :href="&quot;#collapse&quot; + module.id" 
                  :aria-controls="&quot;collapse&quot; + module.id" 
                  aria-expanded="true"
                  data-toggle="collapse" 
                >
                  Uitleg
                </a>
              </div>
            </div>
          </div>
          <div class="col-2 my-auto">
            <div class="card text-center">
              <div class="card-body">
                <p class="card-text">{{ module.week_duration / 8 }} periode</p>
                <p class="card-text">=</p>
                <p class="card-text">{{ module.week_duration }} weken</p>
              </div>
            </div>
          </div>
          <div class="col-2 my-auto">
            <div class="card text-center">
              <div 
                v-if="module.student_modules[0] && module.student_modules[0].begin_date" 
                class="card-body"
              >
                <p class="card-text">&nbsp;</p>
                <p 
                  v-if="module.student_modules[0].begin_date != true" 
                  class="card-text"
                >{{ module.student_modules[0].begin_date }}</p>
                <p class="card-text">&nbsp;</p>
              </div>
              <div 
                v-else 
                class="card-body"
              >
                <p class="card-text">&nbsp;</p>
                <p class="card-text">&nbsp;</p>
                <p class="card-text">&nbsp;</p>
              </div>
            </div>
          </div>
          <div class="col-2 my-auto">
            <div class="card text-center">
              <div 
                v-if="module.student_modules[0]" 
                class="card-body"
              >
                <p class="card-text">{{ module.student_modules[0].finish_date }}</p>
                <p 
                  v-if="module.student_modules[0].begin_date" 
                  class="card-text"
                >Geschatte einddatum: 
                  {{ module.student_modules[0].expected_date }}
                </p>
                <div 
                  v-else 
                  class="card-text"
                >
                  <p>&nbsp;</p>
                  <p>&nbsp;</p>
                </div>
              </div>
              <div 
                v-else 
                class="card-body"
              >
                <p class="card-text">&nbsp;</p>
                <p class="card-text">&nbsp;</p>
                <p class="card-text">&nbsp;</p>
              </div>
            </div>
          </div>
          <div class="col-2 my-auto">
            <div class="card text-center">
              <div 
                v-if="module.student_modules[0]" 
                class="card-body pt-2"
              >
                <p v-if="module.student_modules[0].teacher">
                  ✓ {{ module.student_modules[0].teacher }}
                </p>
                <p v-if="module.student_modules[0].mark">
                  {{ module.student_modules[0].mark }}
                </p>
                <div 
                  v-else 
                  class="card-body"
                >
                  <p class="card-text">&nbsp;</p>
                </div>
              </div>
              <div 
                v-else 
                class="card-body"
              >
                <p class="card-text">&nbsp;</p>
                <p class="card-text">&nbsp;</p>
              </div>
            </div>
          </div>
          <div class="col-2 my-auto">
            <div 
              v-if="module.student_modules[0]" 
              class="card text-center"
            >
              <div 
                v-if="!module.student_modules[0].approved_by" 
                class="card-body"
              >
                <p class="card-text">&nbsp;</p>
                <input 
                  id="checkbox" 
                  v-model="module.student_modules[0].begin_date" 
                  :disabled="submittedBegin" 
                  :value="module.id"
                  type="checkbox" 
                  @click="beginModule(module.id, !module.student_modules[0].begin_date)"
                >
                <label for="checkbox">Gestart?</label>
                <p class="card-text">&nbsp;</p>
              </div>
              <div 
                v-else-if="module.student_modules[0].note" 
                class="card-body p-3"
              >
                <textarea 
                  id="comment" 
                  v-model="module.student_modules[0].note" 
                  class="form-control" 
                  rows="4" 
                  disabled
                />
              </div>
              <div v-else>
                <p class="card-text">&nbsp;</p>
                <p class="card-text">&nbsp;</p>
                <p class="card-text">&nbsp;</p>
                <p class="card-text">&nbsp;</p>
              </div>
            </div>
            <div 
              v-else 
              class="card text-center"
            >
              <div class="card-body">
                <p class="card-text">&nbsp;</p>
                <input 
                  id="checkbox" 
                  :value="module.id"
                  type="checkbox" 
                  @click="beginModule(module.id, true)"
                >
                <label for="checkbox">Gestart?</label>
                <p class="card-text">&nbsp;</p>
              </div>
            </div>   
          </div>
          <div 
            :id="'collapse' + module.id" 
            class="collapse col-12 pt-2" 
            role="tabpanel" 
            aria-labelledby="heading" 
            data-parent="#accordion"
          >
            <div class="card card-body">
              <medium-editor 
                :text="module.long_description" 
                :options="options"
              />
            </div>
          </div>
        </div>
      </div>
      <div  
        v-else
        class="row"
      >
        <div class="col-4">
          <h4>Opleiding voortgang</h4>
          <studentprogresschart 
            :student="student"
            class="h-25"
          />
        </div>
        <div class="col">
          <h4>Cijfers</h4>
          <studentchart 
            :student="student" 
            :is-student="true"
          />
        </div>
      </div>
    </span>
  </div>
</template>
<script>
import editor from "vue2-medium-editor";
import studentchart from "./StudentChart";

export default {
  name: "Studentlist",
  components: {
    "medium-editor": editor,
    studentchart
  },
  data: function() {
    return {
      moduleList: [],
      error: false,
      checked: false,
      options: { disableEditing: true, toolbar: false, placeholder: false },
      showModules: true,
      student: {
        cohorts: [{ id: "", name: "" }],
        user_id: "",
        firstName: "",
        prefix: "",
        lastName: "",
        date: null
      },
      submittedBegin: false
    };
  },
  created: function() {
    this.getStudent();
    this.getModules();
  },
  methods: {
    getModules: function() {
      axios
        .get("/api/student")
        .then(res => {
          this.moduleList = res.data;
          this.submittedBegin = false;
          //sort by approved modules, after that by a started module
          this.moduleList[0].modules = _.sortBy(this.moduleList[0].modules, [
            function(module) {
              if (module.student_modules[0]) {
                return module.student_modules[0].approved_by;
              }
            },
            function(module) {
              if (module.student_modules[0]) {
                return module.student_modules[0].begin_date;
              }
            }
          ]);
        })
        .catch(err => {
          console.log(err);
          this.error = true;
        });
    },
    getStudent: function() {
      axios.get("/api/student/own").then(res => {
        this.student = res.data;
      });
    },
    beginModule: function(moduleID, toggle) {
      this.submittedBegin = true;
      axios
        .put("/api/studentmodule/" + moduleID + "/toggle", { began: toggle })
        .then(res => {
          this.getModules();
        });
    }
  }
};
</script>
