(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-7d3423db"],{"43ca":function(t,e,a){"use strict";var s=a("cc68"),i=a.n(s);i.a},b258:function(t,e,a){"use strict";a.r(e);var s=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"add_group"},[1===parseInt(t.hasWork)?a("div",[a("div",{staticClass:"g_title"},[t._v("工作经历")]),a("div",{staticClass:"fill_group"},[a("div",{staticClass:"fill_line"},[a("div",{staticClass:"f_item"},[t._m(0),a("div",{staticClass:"i_input"},[a("el-input",{attrs:{placeholder:"请输入公司名称"},model:{value:t.work.companyname,callback:function(e){t.$set(t.work,"companyname",e)},expression:"work.companyname"}})],1),a("div",{staticClass:"clear"})]),a("div",{staticClass:"f_item"},[t._m(1),a("div",{staticClass:"i_input"},[a("el-input",{attrs:{placeholder:"请输入职位名称"},model:{value:t.work.jobname,callback:function(e){t.$set(t.work,"jobname",e)},expression:"work.jobname"}})],1),a("div",{staticClass:"clear"})])]),a("div",{staticClass:"fill_line"},[a("div",{staticClass:"f_item"},[t._m(2),a("div",{staticClass:"i_input"},[a("el-date-picker",{style:{width:"250px"},attrs:{type:"month",format:"yyyy-MM","value-format":"yyyy-MM","picker-options":t.datePickerRange,placeholder:"请选择入职时间"},model:{value:t.work.starttime,callback:function(e){t.$set(t.work,"starttime",e)},expression:"work.starttime"}})],1),a("div",{staticClass:"clear"})]),a("div",{staticClass:"f_item"},[t._m(3),a("div",{staticClass:"i_input"},[a("el-date-picker",{style:{width:"250px"},attrs:{disabled:t.work.todate,type:"month",format:"yyyy-MM","value-format":"yyyy-MM","picker-options":t.datePickerRange,placeholder:"请选择离职时间"},model:{value:t.work.endtime,callback:function(e){t.$set(t.work,"endtime",e)},expression:"work.endtime"}}),a("el-checkbox",{staticClass:"for_after",model:{value:t.work.todate,callback:function(e){t.$set(t.work,"todate",e)},expression:"work.todate"}},[t._v("至今")])],1),a("div",{staticClass:"clear"})])]),a("div",{staticClass:"fill_line"},[a("div",{staticClass:"f_item"},[t._m(4),a("div",{staticClass:"i_input"},[a("el-input",{staticClass:"w766 h190",attrs:{type:"textarea",placeholder:"请简要描述您此段工作期间的工作职责"},model:{value:t.work.duty,callback:function(e){t.$set(t.work,"duty",e)},expression:"work.duty"}})],1),a("div",{staticClass:"clear"})])])])]):t._e(),a("div",{staticClass:"g_title"},[t._v("教育经历")]),a("div",{staticClass:"fill_group"},[a("div",{staticClass:"fill_line"},[a("div",{staticClass:"f_item"},[t._m(5),a("div",{staticClass:"i_input"},[a("el-input",{attrs:{placeholder:"请填写就读学校名称"},model:{value:t.education.school,callback:function(e){t.$set(t.education,"school",e)},expression:"education.school"}})],1),a("div",{staticClass:"clear"})]),a("div",{staticClass:"f_item"},[t._m(6),a("div",{staticClass:"i_input"},[a("el-input",{attrs:{placeholder:"请填写就读专业"},model:{value:t.education.major,callback:function(e){t.$set(t.education,"major",e)},expression:"education.major"}})],1),a("div",{staticClass:"clear"})])]),a("div",{staticClass:"fill_line"},[a("div",{staticClass:"f_item"},[t._m(7),a("div",{staticClass:"i_input"},[a("el-date-picker",{style:{width:"250px"},attrs:{type:"month",format:"yyyy-MM","value-format":"yyyy-MM","picker-options":t.datePickerRange,placeholder:"请选择入学时间"},model:{value:t.education.starttime,callback:function(e){t.$set(t.education,"starttime",e)},expression:"education.starttime"}})],1),a("div",{staticClass:"clear"})]),a("div",{staticClass:"f_item"},[t._m(8),a("div",{staticClass:"i_input"},[a("el-date-picker",{style:{width:"250px"},attrs:{disabled:t.education.todate,type:"month",format:"yyyy-MM","value-format":"yyyy-MM","picker-options":t.datePickerRange,placeholder:"请选择毕业时间"},model:{value:t.education.endtime,callback:function(e){t.$set(t.education,"endtime",e)},expression:"education.endtime"}}),a("el-checkbox",{staticClass:"for_after",model:{value:t.education.todate,callback:function(e){t.$set(t.education,"todate",e)},expression:"education.todate"}},[t._v("至今")])],1),a("div",{staticClass:"clear"})])]),a("div",{staticClass:"fill_line"},[a("div",{staticClass:"f_item"},[t._m(9),a("div",{staticClass:"i_input"},[a("el-select",{attrs:{placeholder:"请选择取得学历"},model:{value:t.education.education,callback:function(e){t.$set(t.education,"education",e)},expression:"education.education"}},t._l(t.optionEducation,(function(t,e){return a("el-option",{key:e,attrs:{label:t.text,value:t.id}})})),1)],1),a("div",{staticClass:"clear"})])])]),a("div",{staticClass:"g_title"},[t._v("自我描述")]),a("div",{staticClass:"fill_group"},[a("div",{staticClass:"fill_line"},[a("div",{staticClass:"f_item"},[a("div",{staticClass:"i_label"}),a("div",{staticClass:"i_input"},[a("el-input",{staticClass:"w766 h190",attrs:{type:"textarea",placeholder:"你可以描述你擅长的东西，也可以简单的介绍一下，说说你的求职目标。"},model:{value:t.specialty,callback:function(e){t.specialty=e},expression:"specialty"}})],1),a("div",{staticClass:"clear"})])])]),a("div",{staticClass:"fill_btn"},[a("el-button",{staticClass:"w150",attrs:{type:"primary"},on:{click:t.handleSubmit}},[t._v("完成")])],1)])},i=[function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"i_label"},[a("span",{staticClass:"req"},[t._v("* ")]),t._v("公司名称")])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"i_label"},[a("span",{staticClass:"req"},[t._v("* ")]),t._v("职位名称")])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"i_label"},[a("span",{staticClass:"req"},[t._v("* ")]),t._v("入职时间")])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"i_label"},[a("span",{staticClass:"req"},[t._v("* ")]),t._v("离职时间")])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"i_label"},[a("span",{staticClass:"req"},[t._v("* ")]),t._v("工作职责")])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"i_label"},[a("span",{staticClass:"req"},[t._v("* ")]),t._v("学校名称")])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"i_label"},[a("span",{staticClass:"req"},[t._v("* ")]),t._v("专业名称")])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"i_label"},[a("span",{staticClass:"req"},[t._v("* ")]),t._v("入学时间")])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"i_label"},[a("span",{staticClass:"req"},[t._v("* ")]),t._v("毕业时间")])},function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"i_label"},[a("span",{staticClass:"req"},[t._v("* ")]),t._v("取得学历")])}],n=a("751a"),l=a("d722"),c={name:"AddResumeStep2",data:function(){return{datePickerRange:this.dateRange(),hasWork:0,work:{companyname:"",jobname:"",starttime:"",endtime:"",todate:!1,duty:""},education:{school:"",major:"",starttime:"",endtime:"",todate:!1,education:""},specialty:"",optionEducation:[]}},mounted:function(){var t=this;this.$store.dispatch("getClassify","education").then((function(){t.optionEducation=JSON.parse(JSON.stringify(t.$store.state.classifyEdu))})),this.hasWork=this.$route.params.work},methods:{dateRange:function(){return{disabledDate:function(t){return t.getTime()>(new Date).getTime()}}},handleSubmit:function(){var t=this;if(1===parseInt(this.hasWork)){if(!this.work.companyname)return this.$message({message:"请填写公司名称",type:"warning"}),!1;if(!this.work.jobname)return this.$message({message:"请填写职位名称",type:"warning"}),!1;if(!this.work.starttime)return this.$message({message:"请选择入职时间",type:"warning"}),!1;if(!this.work.todate&&!this.work.endtime)return this.$message({message:"请选择离职时间",type:"warning"}),!1;if(!this.work.duty)return this.$message({message:"请填写工作职责",type:"warning"}),!1}if(!this.education.school)return this.$message({message:"请填写学校名称",type:"warning"}),!1;if(!this.education.major)return this.$message({message:"请填写专业名称",type:"warning"}),!1;if(!this.education.starttime)return this.$message({message:"请选择入学时间",type:"warning"}),!1;if(!this.education.todate&&!this.education.endtime)return this.$message({message:"请选择毕业时间",type:"warning"}),!1;if(!this.education.education)return this.$message({message:"请选择学历",type:"warning"}),!1;if(!this.specialty)return this.$message({message:"请填写自我描述",type:"warning"}),!1;var e={education:{school:this.education.school,major:this.education.major,starttime:this.education.starttime,endtime:this.education.endtime,todate:!0===this.education.todate?1:0,education:this.education.education},specialty:this.specialty};1===parseInt(this.hasWork)&&(e.work={companyname:this.work.companyname,jobname:this.work.jobname,starttime:this.work.starttime,endtime:this.work.endtime,todate:!0===this.work.todate?1:0,duty:this.work.duty}),n["a"].post(l["a"].reg_resume_form_step2,e).then((function(e){200===parseInt(e.code)&&(t.$message({message:e.message,type:"success"}),t.$router.push({path:"/personal/resume/add_resume_step3"}))})).catch((function(){}))}}},r=c,o=(a("43ca"),a("2877")),d=Object(o["a"])(r,s,i,!1,null,"d1f835fe",null);e["default"]=d.exports},cc68:function(t,e,a){}}]);