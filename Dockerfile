FROM php7:nebulas

COPY api /home/api

RUN mkdir -p /home/api/logs && \
    chmod 777 /home/api/logs

ENTRYPOINT ["php", "/home/api/app/app.php"]

CMD ["env=production"]