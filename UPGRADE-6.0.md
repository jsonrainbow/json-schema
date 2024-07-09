UPGRADE FROM 5.3 to 6.0
=======================

## Introduction

We are excited to release version 6.0 of our open-source package, featuring major improvements and important updates. This release includes several breaking changes from version 5.3 aimed at enhancing performance, security, and flexibility.

Please review the following breaking changes carefully and update your implementations to ensure compatibility with version 6.0. This guide provides key modifications and instructions for a smooth transition.

Thank you for your support and contributions to the project.

## Errors
* `constraint` key is no longer the constraint name but contains more information in order to translate violations.

    *Before*
    ```php
    foreach ($validator->getErrors() as $error) {
        echo $error['constraint']; // required
    }
    ```

    *After*
    ```php
    foreach ($validator->getErrors() as $error) {
        echo $error['constraint']['name']; // required
    }
    ```