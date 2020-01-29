import React from 'react';
import queryString from 'query-string'

const { useEffect, memo, useMemo, useState, useReducer } = React;
const initialState = {
    table: null,
    records: [],
    displayable: [],
    updatable: [],
    allow: {},
}
const reducer = (state, action) => {
    switch action.type {
        case "SET_RESPONSE":
            return {
                ...action.payload,
            }
        case "SET_FILTERED_RECORDS":
            let records = state.records
            records = records.filter(row => {
                return Object.keys(row).some(key => {
                    return String(row[key]).toLowerCase().indexOf(action.payload.quickSearchQuery.toLowerCase()) > -1;
                })
            });
            if (action.payload.sort.key) {
                records = _.orderBy(records, i => {
                    let value = i[action.payload.sort.key]
                    if (!isNaN(parseFloat(value)) && isFinite(value)) {
                        return parseFloat(value)
                    }
                    return String(i[action.payload.sort.key]).toLowerCase()
                }, action.payload.sort.order)
            }
            return {
                ...state,
                records
            }
        case default:
            return state
    }
}
export default memo(({endpoint}) => {
    const [state, dispatch] = useReducer(reducer, initialState)
    const [sort, setSort] = useState({
        key: "id",
        order: "asc"
    })
    const [creating, setCreating] = useState({
        active: false,
        form: {},
        errors: []
    })
    const setCreatingRecord = useCallback(data => setCreating(data), [setCreating])

    const [editing, setEditing] = useState({
        form: {},
        errors: [],
        id: null
    })
    // when clicking on the edit button, and since we are looping through the data
    // we can have the current record, so we can set the current form with the current record
    // which we will update, same goes to the id, which we are about to update to send it through request.
    const setEditingRecord = useCallback((data) => setEditing(data), [setEditing])

    const [quickSearchQuery, setQuickSearch] = useState("");
    const setQuickSearchQuery = useCallback((searchQuery) => setQuickSearch(searchQuery), [setQuickSearch])
    const [searchTerms, setSearchTerms] = useState({
        column: "",
        operator: "",
        value: ""
    })
    const [queryParameters, setQueryParameters] = useState("")
    const [limit, setLimit] = useState(10);
    const setSearchLimit = useCallback(limit => setLimit(limit), [setLimit])
    const getRecords = () => {
        fetch(`${endpoint}/?${queryParameters}`)
        .then(response => response.json())
        .then(({ data }) => dspatch({ 
            type: "SET_RESPONSE", 
            payload: data 
        }))

    }
    useEffect(() => {
        getRecords()
    }, [queryParameters])
    const handleQuickSearchThroughRecords = () => {
        dispatch({
            type: "SET_FILTERED_RECORDS",
            payload: {
                quickSearchQuery,
                sort
            }

        })
    }
    const isUpdatable = useCallback((column) => state.updatable.includes(column), [])
    const handleUpdateSubmission = () => {
        fetch(`${endpoint}/${editing.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(editing.form),
        }).then(() => {
            getRecords()
            .then(() => setEditingRecord({ id: null, form: {}, errors: []}))
            .catch(err => {
                if (err.response.status === 422) {
                    setEditingRecord({
                        ...editing,
                        errors: err.response.data
                    })
                }
            })
        })
    }
    const handleDeleteSubmission = (record) => {
        if (!window.confirm('Are you sure you want to delete this')) {
            return;
        }

        fetch(`${endpoint}/${record}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
        }).then(() => {
            getRecords()
        })

    }
    const handleSearchSubmission = () => {
        setQueryParameters(
            queryString.stringify({
                limit,
                ...searchTerms
            })
        )
    }
})