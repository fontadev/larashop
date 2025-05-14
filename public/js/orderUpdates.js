class OrderUpdatesManager {
    constructor(options = {}) {
        this.options = {
            userId: null,
            currentOrderId: null,
            debug: true,
            ...options
        };

        this.init();
    }


    init() {
        if (typeof window.Echo === 'undefined') {
            console.warn('Laravel Echo não está configurado!');
            return;
        }

        this.log('OrderUpdatesManager inicializado');

        if (window.Echo.connector && window.Echo.connector.pusher) {
            window.Echo.connector.pusher.connection.bind('connected', () => {
                this.log('Pusher conectado com sucesso', window.Echo.connector.pusher.connection.socket_id);
            });

            window.Echo.connector.pusher.connection.bind('error', (err) => {
                console.error('Erro na conexão Pusher:', err);
            });
        }

        if (this.options.userId) {
            this.initializeEchoListeners();
        }
    }

    initializeEchoListeners() {
        const userChannel = `user.${this.options.userId}`;
        this.log(`Inscrevendo no canal do usuário: private-${userChannel}`);

        const privateUserChannel = window.Echo.private(userChannel);
        privateUserChannel.listen('.order.updated', (data) => {
            this.log('Evento order.updated recebido via canal do usuário:', data);
            this.handleOrderUpdate(data);
        });

        if (this.options.currentOrderId) {
            const orderChannel = `order.${this.options.currentOrderId}`;
            this.log(`Inscrevendo no canal do pedido: private-${orderChannel}`);

            const privateOrderChannel = window.Echo.private(orderChannel);
            privateOrderChannel.listen('.order.updated', (data) => {
                this.log('Evento order.updated recebido via canal do pedido:', data);
                this.handleOrderUpdate(data);
            });
        }

        if (this.options.debug && window.Echo.connector && window.Echo.connector.pusher) {
            window.Echo.connector.pusher.bind_global((event, data) => {
                this.log(`Evento global recebido:`, event, data);
            });
        }

        this.log('Listeners configurados com sucesso');
    }

    handleOrderUpdate(data) {
        if (this.options.currentOrderId && data.id === this.options.currentOrderId) {
            this.updateOrderDetails(data);
        } else {
            this.updateOrderInList(data);
        }
    }

    updateOrderDetails(data) {
        this.log('Atualizando detalhes do pedido:', data.id);

        const statusBadge = document.querySelector('.order-status-badge');
        this.log('Badge de status encontrado:', !!statusBadge);

        if (statusBadge) {
            this.updateStatusBadge(statusBadge, data.status, data.status_label);

            if (data.status === 'canceled') {
                this.addCancelledAlert();
            }
        } else {
            this.log('ERRO: Não foi possível encontrar o badge de status');
            this.log('Elementos disponíveis:', document.querySelectorAll('span.badge').length);
            document.querySelectorAll('span.badge').forEach(badge => {
                this.log('Badge encontrado:', badge.outerHTML);
            });
        }
    }

    updateOrderInList(data) {
        this.log('Atualizando pedido na lista:', data.id);

        let orderRow = document.querySelector(`tr[data-order-id="${data.id}"]`);

        if (!orderRow) {
            const orderRows = document.querySelectorAll('tbody tr');
            this.log('Linhas de tabela encontradas:', orderRows.length);

            for (const row of orderRows) {
                const idCell = row.querySelector('td:first-child');
                if (idCell && idCell.textContent.trim() === data.id.toString()) {
                    orderRow = row;
                    break;
                }
            }
        }

        this.log('Linha do pedido encontrada:', !!orderRow);

        if (orderRow) {
            const statusCell = orderRow.querySelector('td:nth-child(3)');
            const statusBadge = statusCell ? statusCell.querySelector('span') : null;

            this.log('Badge de status na lista encontrado:', !!statusBadge);

            if (statusBadge) {
                this.updateStatusBadge(statusBadge, data.status, data.status_label);
            } else {
                this.log('ERRO: Não foi possível encontrar o badge de status na linha');
                if (statusCell) {
                    this.log('Conteúdo da célula de status:', statusCell.innerHTML);
                }
            }

            this.highlightRow(orderRow);
        } else {
            this.log('ERRO: Não foi possível encontrar a linha do pedido na tabela');
        }
    }

    updateStatusBadge(badge, status, label) {
        this.log('Atualizando badge para:', status, label);
        this.log('Estado atual do badge:', badge.className, badge.textContent);

        badge.classList.remove('bg-warning', 'bg-success', 'bg-info', 'bg-danger');

        let badgeClass = 'bg-info';
        if (status === 'pending') badgeClass = 'bg-warning';
        if (status === 'processing') badgeClass = 'bg-info';
        if (status === 'completed') badgeClass = 'bg-success';
        if (status === 'canceled') badgeClass = 'bg-danger';

        badge.classList.add(badgeClass);
        badge.textContent = label;

        this.log('Badge atualizado para:', badge.className, badge.textContent);
    }

    highlightRow(row) {
        row.classList.add('table-highlight');
        setTimeout(() => {
            row.classList.remove('table-highlight');
        }, 3000);
    }

    addCancelledAlert() {
        const orderHeader = document.querySelector('.col-md-12.mb-4');

        if (orderHeader && !document.querySelector('.alert-danger')) {
            const cancelAlert = document.createElement('div');
            cancelAlert.className = 'alert alert-danger mt-3';
            cancelAlert.innerHTML =
                '<strong>Pedido Cancelado!</strong> Este pedido foi cancelado e os itens foram retornados ao estoque.';
            orderHeader.appendChild(cancelAlert);
        }
    }

    log(...args) {
        if (this.options.debug) {
            console.log('[OrderUpdatesManager]', ...args);
        }
    }
}

document.querySelector = (function (originalQuerySelector) {
    return function (selector) {
        if (selector && typeof selector === 'string' && selector.includes(':contains')) {
            const parts = selector.split(':contains');
            const baseSelector = parts[0];
            const searchText = parts[1].replace(/[()]/g, '');

            const elements = document.querySelectorAll(baseSelector);

            for (let i = 0; i < elements.length; i++) {
                if (elements[i].textContent.includes(searchText)) {
                    return elements[i];
                }
            }

            return null;
        }

        return originalQuerySelector.call(document, selector);
    };
})(document.querySelector);

window.OrderUpdatesManager = OrderUpdatesManager;
