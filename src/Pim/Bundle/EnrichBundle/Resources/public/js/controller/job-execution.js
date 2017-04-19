'use strict';

define(
    [
        'underscore',
        'pim/controller/base',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title',
        'pim/error',
        'pim/i18n'
    ],
    function (_, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle, Error, i18n) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route) {
                return FetcherRegistry.getFetcher('job-execution').fetch(
                        route.params.id, {id: route.params.id, cached: false}
                    ).then(function (jobExecution) {
                        if (!this.active) {
                            return;
                        }

                        FormBuilder.build('pim-job-execution-form')
                            .then(function (form) {
                                form.setData(jobExecution);
                                form.getRoot().trigger('pim-job-execution-form:start-auto-update', jobExecution);

                                this.on('pim-controller:job-execution:remove', function () {
                                    form.getRoot().trigger('pim-job-execution-form:stop-auto-update');
                                }.bind(this));
                                form.setElement(this.$el).render();
                            }.bind(this));
                    }.bind(this))
                .fail(function (response) {
                    var errorView = new Error(response.responseJSON.message, response.status);
                    errorView.setElement(this.$el).render();
                });
            },

            remove: function () {
                this.trigger('pim-controller:job-execution:remove');

                BaseController.prototype.remove.apply(this, arguments);
            }
        });
    }
);